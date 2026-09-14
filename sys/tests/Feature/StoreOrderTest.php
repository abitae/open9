<?php

use App\Enums\SettingType;
use App\Livewire\Admin\Orders;
use App\Mail\OrderConfirmationMail;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\OrderService;
use App\Services\SiteConfigService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function makeProduct(array $attributes = []): Product
{
    return Product::query()->create(array_merge([
        'name' => 'Servidor Rack 2U',
        'slug' => 'servidor-rack-2u-'.uniqid(),
        'description' => 'Servidor de prueba.',
        'price' => 1500,
        'currency' => 'USD',
        'stock' => 10,
        'rating' => 4.5,
        'sort_order' => 1,
        'status' => 'published',
    ], $attributes));
}

function enableGateway(array $attributes = []): PaymentSetting
{
    return PaymentSetting::query()->updateOrCreate(['id' => 1], array_merge([
        'provider' => 'mercadopago',
        'is_enabled' => true,
        'mode' => 'sandbox',
        'currency' => 'USD',
        'sandbox_access_token' => Crypt::encryptString('TEST-access-token'),
        'sandbox_public_key' => 'TEST-public-key',
    ], $attributes));
}

function setNegativeStockAllowed(bool $allowed): void
{
    Setting::query()->updateOrCreate(
        ['group' => 'store', 'key' => 'allow_negative_stock'],
        [
            'value' => $allowed ? '1' : '0',
            'type' => SettingType::Boolean,
            'is_public' => true,
        ],
    );

    app(SiteConfigService::class)->clearCache();
}

it('starts a checkout, creates a pending order and returns the payment link', function (): void {
    enableGateway();

    Http::fake([
        'api.mercadopago.com/checkout/preferences' => Http::response([
            'id' => 'pref-123',
            'init_point' => 'https://mp/checkout/prod',
            'sandbox_init_point' => 'https://mp/checkout/sandbox',
        ], 200),
    ]);

    $server = makeProduct(['price' => 1500]);
    $license = makeProduct(['price' => 250]);

    $response = $this->postJson('/api/checkout', [
        'buyer' => [
            'name' => 'Grace Hopper',
            'email' => 'grace@example.com',
            'phone' => '999999999',
            'notes' => 'Entregar en oficina.',
        ],
        'items' => [
            ['product_id' => $server->id, 'quantity' => 2],
            ['product_id' => $license->id, 'quantity' => 1],
        ],
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('total', 3250)
        ->assertJsonPath('currency', 'USD')
        ->assertJsonPath('init_point', 'https://mp/checkout/sandbox')
        ->assertJsonPath('public_key', 'TEST-public-key');

    $this->assertDatabaseHas('orders', [
        'buyer_email' => 'grace@example.com',
        'total' => 3250,
        'status' => 'pending',
        'payment_status' => 'unpaid',
        'mercadopago_preference_id' => 'pref-123',
    ]);
});

it('recomputes prices from the database and ignores client-sent totals', function (): void {
    enableGateway();

    Http::fake([
        'api.mercadopago.com/checkout/preferences' => Http::response([
            'id' => 'pref-777',
            'init_point' => 'https://mp/prod',
            'sandbox_init_point' => 'https://mp/sandbox',
        ], 200),
    ]);

    $product = makeProduct(['price' => 1500]);

    $this->postJson('/api/checkout', [
        'buyer' => ['name' => 'Ada', 'email' => 'ada@example.com'],
        'items' => [['product_id' => $product->id, 'quantity' => 2]],
        // Montos manipulados por el cliente que deben ser ignorados.
        'total' => 1,
        'items_total' => 1,
    ])->assertCreated()->assertJsonPath('total', 3000);

    $this->assertDatabaseHas('order_items', [
        'product_id' => $product->id,
        'quantity' => 2,
        'unit_price' => 3000 / 2,
        'subtotal' => 3000,
    ]);
});

it('creates a checkout for a one dollar product', function (): void {
    enableGateway();

    Http::fake([
        'api.mercadopago.com/checkout/preferences' => Http::response([
            'id' => 'pref-one-dollar',
            'init_point' => 'https://mp/prod',
            'sandbox_init_point' => 'https://mp/sandbox',
        ], 200),
    ]);

    $product = makeProduct(['price' => 1, 'stock' => 10]);

    $this->postJson('/api/checkout', [
        'buyer' => ['name' => 'Ada', 'email' => 'ada@example.com'],
        'items' => [['product_id' => $product->id, 'quantity' => 1]],
    ])->assertCreated()->assertJsonPath('total', 1);

    $this->assertDatabaseHas('order_items', [
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_price' => 1,
        'subtotal' => 1,
    ]);
});

it('converts prices to the charge currency (USD product -> PEN)', function (): void {
    enableGateway(['currency' => 'PEN']);

    Http::fake([
        'api.mercadopago.com/checkout/preferences' => Http::response([
            'id' => 'pref-pen',
            'init_point' => 'https://mp/prod',
            'sandbox_init_point' => 'https://mp/sandbox',
        ], 200),
    ]);

    $product = makeProduct(['price' => 100, 'currency' => 'USD']);

    // Sin Setting de tipo de cambio se usa el default 3.75.
    $this->postJson('/api/checkout', [
        'buyer' => ['name' => 'Ada', 'email' => 'ada@example.com'],
        'items' => [['product_id' => $product->id, 'quantity' => 1]],
    ])->assertCreated()
        ->assertJsonPath('currency', 'PEN')
        ->assertJsonPath('total', 375);
});

it('returns 503 when the gateway is disabled', function (): void {
    $product = makeProduct();

    $this->postJson('/api/checkout', [
        'buyer' => ['name' => 'Ada', 'email' => 'ada@example.com'],
        'items' => [['product_id' => $product->id, 'quantity' => 1]],
    ])->assertStatus(503);

    $this->assertDatabaseCount('orders', 0);
});

it('rejects checkout when stock is insufficient and negative stock is disabled', function (): void {
    enableGateway();
    setNegativeStockAllowed(false);

    $product = makeProduct(['stock' => 0]);

    $this->postJson('/api/checkout', [
        'buyer' => ['name' => 'Ada', 'email' => 'ada@example.com'],
        'items' => [['product_id' => $product->id, 'quantity' => 1]],
    ])->assertStatus(422)
        ->assertJsonValidationErrors('items');

    $this->assertDatabaseCount('orders', 0);
});

it('allows checkout at zero stock when negative stock is enabled', function (): void {
    enableGateway();
    setNegativeStockAllowed(true);

    Http::fake([
        'api.mercadopago.com/checkout/preferences' => Http::response([
            'id' => 'pref-neg',
            'init_point' => 'https://mp/prod',
            'sandbox_init_point' => 'https://mp/sandbox',
        ], 200),
    ]);

    $product = makeProduct(['price' => 10, 'stock' => 0]);

    $this->postJson('/api/checkout', [
        'buyer' => ['name' => 'Ada', 'email' => 'ada@example.com'],
        'items' => [['product_id' => $product->id, 'quantity' => 2]],
    ])->assertCreated()->assertJsonPath('total', 20);
});

it('lets paid orders drive stock below zero when negative stock is enabled', function (): void {
    setNegativeStockAllowed(true);

    $product = makeProduct(['price' => 10, 'stock' => 1]);
    $order = app(OrderService::class)->create(
        ['name' => 'Ada', 'email' => 'ada@example.com'],
        [['product_id' => $product->id, 'quantity' => 3]],
        'USD',
    );

    app(OrderService::class)->markAsPaid($order);

    expect((int) $product->fresh()->stock)->toBe(-2);
});

it('does not overwrite unlimited stock after a paid order', function (): void {
    $product = makeProduct(['price' => 10, 'stock' => null]);
    $order = app(OrderService::class)->create(
        ['name' => 'Ada', 'email' => 'ada@example.com'],
        [['product_id' => $product->id, 'quantity' => 2]],
        'USD',
    );

    app(OrderService::class)->markAsPaid($order);

    expect($product->fresh()->stock)->toBeNull();
});

it('rejects checkout that references unpublished products', function (): void {
    enableGateway();
    $draft = makeProduct(['status' => 'draft']);

    $this->postJson('/api/checkout', [
        'buyer' => ['name' => 'Alan Turing', 'email' => 'alan@example.com'],
        'items' => [['product_id' => $draft->id, 'quantity' => 1]],
    ])->assertStatus(422);

    $this->assertDatabaseCount('orders', 0);
});

it('validates buyer and item payloads', function (): void {
    $this->postJson('/api/checkout', [
        'buyer' => ['name' => '', 'email' => 'not-an-email'],
        'items' => [],
    ])->assertStatus(422)
        ->assertJsonValidationErrors(['buyer.name', 'buyer.email', 'items']);
});

it('confirms the order and decrements stock only when the webhook reports approved', function (): void {
    Mail::fake();
    enableGateway();

    $product = makeProduct(['price' => 1500, 'stock' => 5]);
    $order = app(OrderService::class)->create(
        ['name' => 'Katherine Johnson', 'email' => 'katherine@example.com'],
        [['product_id' => $product->id, 'quantity' => 2]],
        'USD',
    );
    $order->update(['mercadopago_preference_id' => 'pref-abc']);

    Http::fake([
        'api.mercadopago.com/v1/payments/*' => Http::response([
            'id' => 987654,
            'status' => 'approved',
            'external_reference' => $order->order_code,
            'transaction_amount' => 3000,
            'currency_id' => 'USD',
        ], 200),
    ]);

    $this->postJson('/api/webhooks/mercadopago', [
        'type' => 'payment',
        'data' => ['id' => '987654'],
    ])->assertOk();

    $order->refresh();
    expect($order->payment_status)->toBe('paid');
    expect($order->status)->toBe('confirmed');
    expect((int) $product->fresh()->stock)->toBe(3);

    $this->assertDatabaseHas('order_payments', [
        'order_id' => $order->id,
        'provider_payment_id' => '987654',
        'status' => 'approved',
    ]);

    Mail::assertSent(OrderConfirmationMail::class, function (OrderConfirmationMail $mail) use ($order): bool {
        return $mail->hasTo('katherine@example.com') && $mail->order->is($order);
    });
});

it('is idempotent: a repeated approved webhook does not decrement stock twice', function (): void {
    Mail::fake();
    enableGateway();

    $product = makeProduct(['price' => 1500, 'stock' => 5]);
    $order = app(OrderService::class)->create(
        ['name' => 'Katherine Johnson', 'email' => 'katherine@example.com'],
        [['product_id' => $product->id, 'quantity' => 2]],
        'USD',
    );

    Http::fake([
        'api.mercadopago.com/v1/payments/*' => Http::response([
            'id' => 987654,
            'status' => 'approved',
            'external_reference' => $order->order_code,
            'transaction_amount' => 3000,
            'currency_id' => 'USD',
        ], 200),
    ]);

    $payload = ['type' => 'payment', 'data' => ['id' => '987654']];

    $this->postJson('/api/webhooks/mercadopago', $payload)->assertOk();
    $this->postJson('/api/webhooks/mercadopago', $payload)->assertOk();

    expect((int) $product->fresh()->stock)->toBe(3);

    Mail::assertSent(OrderConfirmationMail::class, 1);
});

it('processes an in-site Bricks payment and confirms the order when approved', function (): void {
    Mail::fake();
    enableGateway();

    $product = makeProduct(['price' => 1500, 'stock' => 5]);

    Http::fake([
        'api.mercadopago.com/checkout/preferences' => Http::response([
            'id' => 'pref-brick',
            'init_point' => 'https://mp/prod',
            'sandbox_init_point' => 'https://mp/sandbox',
        ], 200),
        'api.mercadopago.com/v1/payments' => Http::response([
            'id' => 111222,
            'status' => 'approved',
            'status_detail' => 'accredited',
            'transaction_amount' => 3000,
            'currency_id' => 'USD',
        ], 201),
    ]);

    $orderCode = $this->postJson('/api/checkout', [
        'buyer' => ['name' => 'Ada', 'email' => 'ada@example.com'],
        'items' => [['product_id' => $product->id, 'quantity' => 2]],
    ])->assertCreated()->json('order_code');

    $this->postJson('/api/checkout/process', [
        'order_code' => $orderCode,
        'form_data' => [
            'token' => 'card_token_123',
            'payment_method_id' => 'visa',
            'issuer_id' => '310',
            'installments' => 1,
            'payer' => ['email' => 'ada@example.com'],
        ],
    ])->assertOk()
        ->assertJsonPath('status', 'approved')
        ->assertJsonPath('payment_status', 'paid');

    $this->assertDatabaseHas('orders', [
        'order_code' => $orderCode,
        'payment_status' => 'paid',
        'status' => 'confirmed',
    ]);

    expect((int) $product->fresh()->stock)->toBe(3);

    $this->assertDatabaseHas('order_payments', [
        'provider_payment_id' => '111222',
        'status' => 'approved',
    ]);

    Mail::assertSent(OrderConfirmationMail::class, function (OrderConfirmationMail $mail): bool {
        return $mail->hasTo('ada@example.com');
    });
});

it('marks the order as failed when the Bricks payment is rejected', function (): void {
    Mail::fake();
    enableGateway();

    $product = makeProduct(['price' => 1500, 'stock' => 5]);

    Http::fake([
        'api.mercadopago.com/checkout/preferences' => Http::response([
            'id' => 'pref-rej',
            'init_point' => 'https://mp/prod',
            'sandbox_init_point' => 'https://mp/sandbox',
        ], 200),
        'api.mercadopago.com/v1/payments' => Http::response([
            'id' => 333444,
            'status' => 'rejected',
            'status_detail' => 'cc_rejected_other_reason',
            'transaction_amount' => 3000,
            'currency_id' => 'USD',
        ], 201),
    ]);

    $orderCode = $this->postJson('/api/checkout', [
        'buyer' => ['name' => 'Ada', 'email' => 'ada@example.com'],
        'items' => [['product_id' => $product->id, 'quantity' => 2]],
    ])->assertCreated()->json('order_code');

    $this->postJson('/api/checkout/process', [
        'order_code' => $orderCode,
        'form_data' => [
            'token' => 'card_token_x',
            'payment_method_id' => 'visa',
            'installments' => 1,
            'payer' => ['email' => 'ada@example.com'],
        ],
    ])->assertOk()
        ->assertJsonPath('status', 'rejected')
        ->assertJsonPath('payment_status', 'failed');

    expect((int) $product->fresh()->stock)->toBe(5);

    Mail::assertNothingSent();
});

it('validates the Bricks payment payload', function (): void {
    enableGateway();

    $this->postJson('/api/checkout/process', ['order_code' => 'NOPE'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['order_code', 'form_data']);
});

it('exposes the order status for the result page', function (): void {
    $product = makeProduct();
    $order = app(OrderService::class)->create(
        ['name' => 'Ada', 'email' => 'ada@example.com'],
        [['product_id' => $product->id, 'quantity' => 1]],
        'USD',
    );

    $this->getJson('/api/orders/'.$order->order_code)
        ->assertOk()
        ->assertJsonPath('order_code', $order->order_code)
        ->assertJsonPath('payment_status', 'unpaid')
        ->assertJsonPath('status', 'pending');
});

it('encrypts stored gateway credentials and decrypts them on demand', function (): void {
    $settings = enableGateway();

    // El valor persistido está cifrado (no es texto plano).
    expect($settings->getAttributes()['sandbox_access_token'])->not->toBe('TEST-access-token');
    expect($settings->resolvedAccessToken())->toBe('TEST-access-token');
    expect($settings->resolvedPublicKey())->toBe('TEST-public-key');
});

it('does not confirm an order when the webhook signature is invalid', function (): void {
    Mail::fake();
    enableGateway([
        'webhook_secret' => Crypt::encryptString('whsec-test'),
    ]);

    $product = makeProduct(['price' => 1500, 'stock' => 5]);
    $order = app(OrderService::class)->create(
        ['name' => 'Katherine Johnson', 'email' => 'katherine@example.com'],
        [['product_id' => $product->id, 'quantity' => 2]],
        'USD',
    );

    Http::fake([
        'api.mercadopago.com/v1/payments/*' => Http::response([
            'id' => 987654,
            'status' => 'approved',
            'external_reference' => $order->order_code,
            'transaction_amount' => 3000,
            'currency_id' => 'USD',
        ], 200),
    ]);

    $this->postJson('/api/webhooks/mercadopago', [
        'type' => 'payment',
        'data' => ['id' => '987654'],
    ], [
        'x-signature' => 'ts=1,v1=invalid-signature',
        'x-request-id' => 'req-1',
    ])->assertOk();

    expect($order->fresh()->payment_status)->toBe('unpaid');
    Mail::assertNothingSent();
});

it('rejects unsigned webhooks in production when no secret is configured', function (): void {
    Mail::fake();
    enableGateway();
    $this->app['env'] = 'production';

    $product = makeProduct(['price' => 10, 'stock' => 5]);
    $order = app(OrderService::class)->create(
        ['name' => 'Ada', 'email' => 'ada@example.com'],
        [['product_id' => $product->id, 'quantity' => 1]],
        'USD',
    );

    Http::fake([
        'api.mercadopago.com/v1/payments/*' => Http::response([
            'id' => 555,
            'status' => 'approved',
            'external_reference' => $order->order_code,
            'transaction_amount' => 10,
            'currency_id' => 'USD',
        ], 200),
    ]);

    $this->postJson('/api/webhooks/mercadopago', [
        'type' => 'payment',
        'data' => ['id' => '555'],
    ])->assertOk();

    expect($order->fresh()->payment_status)->toBe('unpaid');
    Mail::assertNothingSent();
});

it('does not mark an order paid when the MercadoPago amount does not match', function (): void {
    Mail::fake();
    enableGateway();

    $product = makeProduct(['price' => 1500, 'stock' => 5]);
    $order = app(OrderService::class)->create(
        ['name' => 'Ada', 'email' => 'ada@example.com'],
        [['product_id' => $product->id, 'quantity' => 2]],
        'USD',
    );

    Http::fake([
        'api.mercadopago.com/v1/payments/*' => Http::response([
            'id' => 777,
            'status' => 'approved',
            'external_reference' => $order->order_code,
            'transaction_amount' => 1,
            'currency_id' => 'USD',
        ], 200),
    ]);

    $this->postJson('/api/webhooks/mercadopago', [
        'type' => 'payment',
        'data' => ['id' => '777'],
    ])->assertOk();

    expect($order->fresh()->payment_status)->toBe('unpaid');
    expect((int) $product->fresh()->stock)->toBe(5);
    Mail::assertNothingSent();
});

it('shows the ordered products inside the admin detail modal', function (): void {
    $this->seed(DatabaseSeeder::class);
    $admin = User::query()->where('email', 'admin@open9.dev')->firstOrFail();

    $product = makeProduct(['name' => 'Firewall UTM Pro', 'price' => 890]);
    $order = app(OrderService::class)->create(
        ['name' => 'Katherine Johnson', 'email' => 'katherine@example.com'],
        [['product_id' => $product->id, 'quantity' => 3]],
    );

    Livewire::actingAs($admin)
        ->test(Orders::class)
        ->call('detail', $order->getKey())
        ->assertSee('Productos')
        ->assertSee('Firewall UTM Pro')
        ->assertSee('katherine@example.com');
});
