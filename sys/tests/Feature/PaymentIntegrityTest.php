<?php

use App\Mail\OrderConfirmationMail;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

function integrityTestProduct(array $attributes = []): Product
{
    return Product::query()->create(array_merge([
        'name' => 'Firewall UTM',
        'slug' => 'firewall-integrity-'.uniqid(),
        'description' => 'Producto de integridad.',
        'price' => 100,
        'currency' => 'USD',
        'stock' => 5,
        'rating' => 4.5,
        'sort_order' => 1,
        'status' => 'published',
    ], $attributes));
}

it('stores an integrity hash when creating an order', function (): void {
    $product = integrityTestProduct();
    $order = app(OrderService::class)->create(
        ['name' => 'Ada Lovelace', 'email' => 'ada@example.com'],
        [['product_id' => $product->id, 'quantity' => 2]],
        'USD',
    );

    expect($order->integrity_hash)->toBeString()->toHaveLength(64);
});

it('does not mark a tampered total as paid', function (): void {
    Mail::fake();

    $product = integrityTestProduct();
    $order = app(OrderService::class)->create(
        ['name' => 'Ada Lovelace', 'email' => 'ada@example.com'],
        [['product_id' => $product->id, 'quantity' => 2]],
        'USD',
    );

    $order->update(['total' => 1]);

    expect(app(OrderService::class)->markAsPaid($order->fresh()))->toBeFalse();
    expect($order->fresh()->payment_status)->toBe('unpaid');
    expect((int) $product->fresh()->stock)->toBe(5);
    Mail::assertNothingSent();
});

it('does not mark a tampered item as paid', function (): void {
    Mail::fake();

    $product = integrityTestProduct();
    $order = app(OrderService::class)->create(
        ['name' => 'Ada Lovelace', 'email' => 'ada@example.com'],
        [['product_id' => $product->id, 'quantity' => 2]],
        'USD',
    );

    $order->items()->first()->update([
        'unit_price' => 1,
        'subtotal' => 1,
    ]);

    expect(app(OrderService::class)->markAsPaid($order->fresh('items')))->toBeFalse();
    expect($order->fresh()->payment_status)->toBe('unpaid');
    expect((int) $product->fresh()->stock)->toBe(5);
    Mail::assertNothingSent();
});

it('confirms payment when the integrity hash is intact', function (): void {
    Mail::fake();

    $product = integrityTestProduct();
    $order = app(OrderService::class)->create(
        ['name' => 'Ada Lovelace', 'email' => 'ada@example.com'],
        [['product_id' => $product->id, 'quantity' => 2]],
        'USD',
    );

    $hash = $order->integrity_hash;

    expect(app(OrderService::class)->markAsPaid($order))->toBeTrue();
    expect($order->fresh()->payment_status)->toBe('paid');
    expect($order->fresh()->integrity_hash)->toBe($hash);
    Mail::assertSent(OrderConfirmationMail::class);
});

it('does not change the integrity hash on a repeated markAsPaid', function (): void {
    $product = integrityTestProduct();
    $order = app(OrderService::class)->create(
        ['name' => 'Ada Lovelace', 'email' => 'ada@example.com'],
        [['product_id' => $product->id, 'quantity' => 1]],
        'USD',
    );

    $service = app(OrderService::class);
    expect($service->markAsPaid($order))->toBeTrue();

    $hash = $order->fresh()->integrity_hash;

    expect($service->markAsPaid($order->fresh()))->toBeFalse();
    expect($order->fresh()->integrity_hash)->toBe($hash);
});

it('backfills a missing integrity hash on the first payment', function (): void {
    $product = integrityTestProduct();
    $order = app(OrderService::class)->create(
        ['name' => 'Ada Lovelace', 'email' => 'ada@example.com'],
        [['product_id' => $product->id, 'quantity' => 1]],
        'USD',
    );

    $order->update(['integrity_hash' => null]);

    expect(app(OrderService::class)->markAsPaid($order->fresh()))->toBeTrue();
    expect($order->fresh()->integrity_hash)->toBeString()->toHaveLength(64);
    expect($order->fresh()->payment_status)->toBe('paid');
});
