<?php

use App\Mail\ClientVerificationCodeMail;
use App\Models\Client;
use App\Models\PaymentSetting;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

function emailVerificationProduct(array $attributes = []): Product
{
    return Product::query()->create(array_merge([
        'name' => 'Servidor Rack 2U',
        'slug' => 'servidor-verify-'.uniqid(),
        'description' => 'Servidor de prueba.',
        'price' => 100,
        'currency' => 'USD',
        'stock' => 10,
        'rating' => 4.5,
        'sort_order' => 1,
        'status' => 'published',
    ], $attributes));
}

function enableEmailVerificationPayments(): void
{
    PaymentSetting::query()->updateOrCreate(['id' => 1], [
        'provider' => 'mercadopago',
        'is_enabled' => true,
        'mode' => 'sandbox',
        'currency' => 'USD',
        'sandbox_access_token' => Crypt::encryptString('TEST-access-token'),
        'sandbox_public_key' => 'TEST-public-key',
    ]);
}

function queuedVerificationCode(string $email): string
{
    $code = '';

    Mail::assertQueued(ClientVerificationCodeMail::class, function (ClientVerificationCodeMail $mail) use ($email, &$code): bool {
        if (! $mail->hasTo($email)) {
            return false;
        }

        $code = $mail->code;

        return true;
    });

    expect($code)->toMatch('/^\d{6}$/');

    return $code;
}

it('registers a client without a token and queues a verification code', function (): void {
    Mail::fake();

    $response = $this->postJson('/api/auth/register', [
        'name' => 'Grace Hopper',
        'email' => 'grace@example.com',
        'password' => 'secret-password',
        'phone' => '999888777',
    ]);

    $response->assertCreated()
        ->assertJsonPath('requires_verification', true)
        ->assertJsonPath('email', 'grace@example.com');

    expect($response->json('token'))->toBeNull();

    $client = Client::query()->where('email', 'grace@example.com')->firstOrFail();
    expect($client->email_verified_at)->toBeNull();
    expect(Hash::check('secret-password', $client->password))->toBeTrue();
    expect($client->tokens()->count())->toBe(0);

    queuedVerificationCode('grace@example.com');
});

it('issues a token when the verification code is correct', function (): void {
    Mail::fake();

    $this->postJson('/api/auth/register', [
        'name' => 'Grace Hopper',
        'email' => 'grace@example.com',
        'password' => 'secret-password',
    ])->assertCreated();

    $code = queuedVerificationCode('grace@example.com');

    $this->postJson('/api/auth/verify-email', [
        'email' => 'grace@example.com',
        'code' => $code,
    ])->assertOk()
        ->assertJsonStructure(['token', 'client'])
        ->assertJsonPath('client.email_verified', true);

    $client = Client::query()->where('email', 'grace@example.com')->firstOrFail();
    expect($client->hasVerifiedEmail())->toBeTrue();
    expect($client->tokens()->count())->toBe(1);
});

it('rejects an invalid verification code without issuing a token', function (): void {
    Mail::fake();

    $this->postJson('/api/auth/register', [
        'name' => 'Grace Hopper',
        'email' => 'grace@example.com',
        'password' => 'secret-password',
    ])->assertCreated();

    $this->postJson('/api/auth/verify-email', [
        'email' => 'grace@example.com',
        'code' => '000000',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['code']);

    $client = Client::query()->where('email', 'grace@example.com')->firstOrFail();
    expect($client->email_verified_at)->toBeNull();
    expect($client->tokens()->count())->toBe(0);
});

it('rejects an expired verification code', function (): void {
    Mail::fake();

    $this->postJson('/api/auth/register', [
        'name' => 'Grace Hopper',
        'email' => 'grace@example.com',
        'password' => 'secret-password',
    ])->assertCreated();

    $code = queuedVerificationCode('grace@example.com');

    $this->travel(16)->minutes();

    $this->postJson('/api/auth/verify-email', [
        'email' => 'grace@example.com',
        'code' => $code,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['code']);
});

it('does not issue a token when logging in before verifying the email', function (): void {
    $client = Client::factory()->unverified()->create([
        'email' => 'ada@example.com',
        'password' => 'correct-horse',
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'ada@example.com',
        'password' => 'correct-horse',
    ]);

    $response->assertForbidden()
        ->assertJsonPath('requires_verification', true)
        ->assertJsonPath('email', 'ada@example.com');

    expect($response->json('token'))->toBeNull();
    expect($client->tokens()->count())->toBe(0);
});

it('allows login after the email is verified', function (): void {
    Mail::fake();

    $this->postJson('/api/auth/register', [
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'password' => 'correct-horse',
    ])->assertCreated();

    $code = queuedVerificationCode('ada@example.com');

    $this->postJson('/api/auth/verify-email', [
        'email' => 'ada@example.com',
        'code' => $code,
    ])->assertOk();

    $this->postJson('/api/auth/login', [
        'email' => 'ada@example.com',
        'password' => 'correct-horse',
    ])->assertOk()->assertJsonStructure(['token', 'client']);
});

it('blocks account endpoints for unverified clients', function (): void {
    $client = Client::factory()->unverified()->create();
    $token = $client->createToken('spa')->plainTextToken;

    $this->withToken($token)->getJson('/api/auth/me')->assertForbidden();
    $this->withToken($token)->getJson('/api/account/profile')->assertForbidden();
    $this->withToken($token)->getJson('/api/account/orders')->assertForbidden();
});

it('blocks authenticated checkout until the email is verified', function (): void {
    enableEmailVerificationPayments();

    Http::fake([
        'api.mercadopago.com/checkout/preferences' => Http::response([
            'id' => 'pref-unverified',
            'init_point' => 'https://mp/prod',
            'sandbox_init_point' => 'https://mp/sandbox',
        ], 200),
    ]);

    $product = emailVerificationProduct(['price' => 100]);
    $client = Client::factory()->unverified()->create();
    $token = $client->createToken('spa')->plainTextToken;

    $this->withToken($token)->postJson('/api/checkout', [
        'buyer' => ['name' => $client->name, 'email' => $client->email],
        'items' => [['product_id' => $product->id, 'quantity' => 1]],
    ])->assertForbidden();

    $this->assertDatabaseCount('orders', 0);
});

it('still allows guest checkout without a token', function (): void {
    enableEmailVerificationPayments();

    Http::fake([
        'api.mercadopago.com/checkout/preferences' => Http::response([
            'id' => 'pref-guest-verify',
            'init_point' => 'https://mp/prod',
            'sandbox_init_point' => 'https://mp/sandbox',
        ], 200),
    ]);

    $product = emailVerificationProduct(['price' => 100]);

    $this->postJson('/api/checkout', [
        'buyer' => ['name' => 'Invitado', 'email' => 'guest@example.com'],
        'items' => [['product_id' => $product->id, 'quantity' => 1]],
    ])->assertCreated();
});

it('resends a verification code after the cooldown', function (): void {
    Mail::fake();

    $this->postJson('/api/auth/register', [
        'name' => 'Grace Hopper',
        'email' => 'grace@example.com',
        'password' => 'secret-password',
    ])->assertCreated();

    $this->postJson('/api/auth/resend-verification', [
        'email' => 'grace@example.com',
    ])->assertUnprocessable();

    $this->travel(61)->seconds();

    $this->postJson('/api/auth/resend-verification', [
        'email' => 'grace@example.com',
    ])->assertOk();

    Mail::assertQueued(ClientVerificationCodeMail::class, 2);
});
