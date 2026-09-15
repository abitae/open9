<?php

use App\Mail\ClientVerificationCodeMail;
use App\Models\Client;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Models\SocialLoginSetting;
use App\Services\OrderService;
use App\Services\SiteConfigService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\User;

uses(RefreshDatabase::class);

function storeProduct(array $attributes = []): Product
{
    return Product::query()->create(array_merge([
        'name' => 'Servidor Rack 2U',
        'slug' => 'servidor-'.uniqid(),
        'description' => 'Servidor de prueba.',
        'price' => 100,
        'currency' => 'USD',
        'stock' => 10,
        'rating' => 4.5,
        'sort_order' => 1,
        'status' => 'published',
    ], $attributes));
}

function enablePaymentGateway(): void
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

function enableGoogleLogin(): SocialLoginSetting
{
    return SocialLoginSetting::query()->updateOrCreate(['id' => 1], [
        'google_enabled' => true,
        'google_client_id' => 'client-id.apps.googleusercontent.com',
        'google_client_secret' => Crypt::encryptString('super-secret'),
        'google_redirect_url' => 'http://localhost/api/auth/google/callback',
    ]);
}

it('registers a client and requires email verification before issuing a token', function (): void {
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

    $this->assertDatabaseHas('clients', ['email' => 'grace@example.com']);

    $client = Client::query()->where('email', 'grace@example.com')->firstOrFail();
    expect($client->password)->not->toBe('secret-password');
    expect($client->email_verified_at)->toBeNull();
    expect(Hash::check('secret-password', $client->password))->toBeTrue();
    Mail::assertQueued(ClientVerificationCodeMail::class);
});

it('rejects duplicated emails on register', function (): void {
    Client::factory()->create(['email' => 'grace@example.com']);

    $this->postJson('/api/auth/register', [
        'name' => 'Otra Grace',
        'email' => 'grace@example.com',
        'password' => 'secret-password',
    ])->assertStatus(422)->assertJsonValidationErrors(['email']);
});

it('logs a client in with valid credentials', function (): void {
    Client::factory()->create([
        'email' => 'ada@example.com',
        'password' => Hash::make('correct-horse'),
    ]);

    $this->postJson('/api/auth/login', [
        'email' => 'ada@example.com',
        'password' => 'correct-horse',
    ])->assertOk()->assertJsonStructure(['token', 'client']);
});

it('rejects invalid credentials', function (): void {
    Client::factory()->create([
        'email' => 'ada@example.com',
        'password' => Hash::make('correct-horse'),
    ]);

    $this->postJson('/api/auth/login', [
        'email' => 'ada@example.com',
        'password' => 'wrong',
    ])->assertStatus(422)->assertJsonValidationErrors(['email']);
});

it('returns the authenticated client on /me and clears token on logout', function (): void {
    $client = Client::factory()->create();
    $token = $client->createToken('spa')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/auth/me')
        ->assertOk()
        ->assertJsonPath('client.id', $client->id);

    $this->withToken($token)->postJson('/api/auth/logout')->assertOk();

    expect($client->tokens()->count())->toBe(0);

    // Limpiamos el guard resuelto en memoria (en producción cada request es
    // una app nueva) para verificar que el token revocado ya no autentica.
    $this->app['auth']->forgetGuards();
    $this->withToken($token)->getJson('/api/auth/me')->assertStatus(401);
});

it('lists only the orders that belong to the authenticated client', function (): void {
    $product = storeProduct();
    $client = Client::factory()->create();
    $other = Client::factory()->create();

    $mine = app(OrderService::class)->create(
        ['name' => $client->name, 'email' => $client->email],
        [['product_id' => $product->id, 'quantity' => 1]],
        'USD',
        $client,
    );

    app(OrderService::class)->create(
        ['name' => $other->name, 'email' => $other->email],
        [['product_id' => $product->id, 'quantity' => 1]],
        'USD',
        $other,
    );

    $token = $client->createToken('spa')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/account/orders')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.order_code', $mine->order_code);
});

it('does not expose another client order detail', function (): void {
    $product = storeProduct();
    $client = Client::factory()->create();
    $other = Client::factory()->create();

    $foreign = app(OrderService::class)->create(
        ['name' => $other->name, 'email' => $other->email],
        [['product_id' => $product->id, 'quantity' => 1]],
        'USD',
        $other,
    );

    $token = $client->createToken('spa')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/account/orders/'.$foreign->order_code)
        ->assertStatus(404);
});

it('manages client addresses with a single default', function (): void {
    $client = Client::factory()->create();
    $token = $client->createToken('spa')->plainTextToken;

    $first = $this->withToken($token)->postJson('/api/account/addresses', [
        'recipient_name' => 'Grace Hopper',
        'line1' => 'Av. Siempre Viva 123',
        'city' => 'Lima',
        'country' => 'PE',
    ])->assertCreated()->json('address');

    // La primera dirección se marca como predeterminada automáticamente.
    expect($first['is_default'])->toBeTrue();

    $second = $this->withToken($token)->postJson('/api/account/addresses', [
        'recipient_name' => 'Grace Oficina',
        'line1' => 'Jr. Union 456',
        'city' => 'Lima',
        'country' => 'PE',
        'is_default' => true,
    ])->assertCreated()->json('address');

    $this->withToken($token)->getJson('/api/account/addresses')
        ->assertOk()
        ->assertJsonCount(2, 'addresses');

    // Solo la segunda queda como predeterminada.
    $this->assertDatabaseHas('client_addresses', ['id' => $second['id'], 'is_default' => true]);
    $this->assertDatabaseHas('client_addresses', ['id' => $first['id'], 'is_default' => false]);

    // setDefault regresa el default a la primera.
    $this->withToken($token)->postJson('/api/account/addresses/'.$first['id'].'/default')->assertOk();
    $this->assertDatabaseHas('client_addresses', ['id' => $first['id'], 'is_default' => true]);
    $this->assertDatabaseHas('client_addresses', ['id' => $second['id'], 'is_default' => false]);

    // Actualizar y eliminar.
    $this->withToken($token)->putJson('/api/account/addresses/'.$second['id'], [
        'recipient_name' => 'Grace Actualizada',
        'line1' => 'Jr. Union 456',
        'city' => 'Arequipa',
        'country' => 'PE',
    ])->assertOk()->assertJsonPath('address.city', 'Arequipa');

    $this->withToken($token)->deleteJson('/api/account/addresses/'.$second['id'])->assertOk();
    $this->assertDatabaseMissing('client_addresses', ['id' => $second['id']]);
});

it('cannot manage addresses of other clients', function (): void {
    $client = Client::factory()->create();
    $other = Client::factory()->create();
    $foreignAddress = $other->addresses()->create([
        'recipient_name' => 'Otro',
        'line1' => 'Calle 1',
        'city' => 'Lima',
        'country' => 'PE',
    ]);

    $token = $client->createToken('spa')->plainTextToken;

    $this->withToken($token)
        ->putJson('/api/account/addresses/'.$foreignAddress->id, [
            'recipient_name' => 'Hackeado',
            'line1' => 'Calle 1',
            'city' => 'Lima',
            'country' => 'PE',
        ])->assertStatus(404);
});

it('requires authentication for account endpoints', function (): void {
    $this->getJson('/api/account/profile')->assertUnauthorized();
    $this->getJson('/api/account/orders')->assertUnauthorized();
    $this->getJson('/api/account/addresses')->assertUnauthorized();
});

it('rejects revoked tokens on account endpoints', function (): void {
    $client = Client::factory()->create();
    $token = $client->createToken('spa')->plainTextToken;
    $client->tokens()->delete();

    $this->withToken($token)->getJson('/api/account/profile')->assertUnauthorized();
    $this->withToken($token)->getJson('/api/account/orders')->assertUnauthorized();
    $this->withToken($token)->getJson('/api/account/addresses')->assertUnauthorized();
});

it('updates the client profile', function (): void {
    $client = Client::factory()->create(['name' => 'Nombre Viejo']);
    $token = $client->createToken('spa')->plainTextToken;

    $this->withToken($token)->putJson('/api/account/profile', [
        'name' => 'Nombre Nuevo',
        'email' => $client->email,
        'phone' => '111222333',
    ])->assertOk()->assertJsonPath('client.name', 'Nombre Nuevo');

    $this->assertDatabaseHas('clients', ['id' => $client->id, 'name' => 'Nombre Nuevo', 'phone' => '111222333']);
});

it('associates the client id when checking out with a token', function (): void {
    enablePaymentGateway();

    Http::fake([
        'api.mercadopago.com/checkout/preferences' => Http::response([
            'id' => 'pref-client',
            'init_point' => 'https://mp/prod',
            'sandbox_init_point' => 'https://mp/sandbox',
        ], 200),
    ]);

    $product = storeProduct(['price' => 100]);
    $client = Client::factory()->create();
    $token = $client->createToken('spa')->plainTextToken;

    $this->withToken($token)->postJson('/api/checkout', [
        'buyer' => ['name' => $client->name, 'email' => $client->email],
        'items' => [['product_id' => $product->id, 'quantity' => 1]],
        'shipping_address' => [
            'recipient_name' => $client->name,
            'line1' => 'Av. Test 123',
            'city' => 'Lima',
            'country' => 'PE',
        ],
    ])->assertCreated();

    $this->assertDatabaseHas('orders', [
        'client_id' => $client->id,
        'buyer_email' => $client->email,
    ]);
});

it('keeps guest checkout working without a token', function (): void {
    enablePaymentGateway();

    Http::fake([
        'api.mercadopago.com/checkout/preferences' => Http::response([
            'id' => 'pref-guest',
            'init_point' => 'https://mp/prod',
            'sandbox_init_point' => 'https://mp/sandbox',
        ], 200),
    ]);

    $product = storeProduct(['price' => 100]);

    $this->postJson('/api/checkout', [
        'buyer' => ['name' => 'Invitado', 'email' => 'guest@example.com'],
        'items' => [['product_id' => $product->id, 'quantity' => 1]],
    ])->assertCreated();

    $this->assertDatabaseHas('orders', [
        'client_id' => null,
        'buyer_email' => 'guest@example.com',
    ]);
});

it('exposes whether google login is enabled in the site payload', function (): void {
    enableGoogleLogin();

    $this->getJson('/api/site')
        ->assertOk()
        ->assertJsonPath('auth.google_enabled', true);
});

it('enables google login from env credentials when the admin toggle is off', function (): void {
    config([
        'services.google.client_id' => 'env-client.apps.googleusercontent.com',
        'services.google.client_secret' => 'env-secret',
    ]);
    app(SiteConfigService::class)->clearCache();

    $this->getJson('/api/site')
        ->assertOk()
        ->assertJsonPath('auth.google_enabled', true);
});

it('creates and logs in a client through the Google callback', function (): void {
    Mail::fake();
    enableGoogleLogin();

    $googleUser = new User;
    $googleUser->map([
        'id' => 'google-999',
        'name' => 'Google Client',
        'email' => 'google-client@example.com',
        'avatar' => 'https://lh3.googleusercontent.com/avatar',
    ]);

    $provider = Mockery::mock(AbstractProvider::class);
    $provider->shouldReceive('stateless')->andReturnSelf();
    $provider->shouldReceive('user')->andReturn($googleUser);
    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    // El callback exige un `state` que coincida con la caché de redirect()
    // y con la cookie del navegador que inició el flujo.
    $state = 'valid-state-for-new-client';
    Cache::put('google_oauth_return:'.hash('sha256', $state), 'http://localhost', now()->addMinutes(10));

    $response = $this->withUnencryptedCookie('oauth_state', $state)
        ->get('/api/auth/google/callback?code=fake-code&state='.$state);

    $response->assertRedirect();
    expect($response->headers->get('Location'))->toContain('/auth/callback?token=');

    $this->assertDatabaseHas('clients', [
        'email' => 'google-client@example.com',
        'google_id' => 'google-999',
    ]);

    $client = Client::query()->where('email', 'google-client@example.com')->firstOrFail();
    expect($client->email_verified_at)->not->toBeNull();
    Mail::assertNothingOutgoing();
});

it('redirects google callback to the frontend origin that started login', function (): void {
    enableGoogleLogin();

    $googleUser = new User;
    $googleUser->map([
        'id' => 'google-888',
        'name' => 'Port Client',
        'email' => 'port-client@example.com',
        'avatar' => null,
    ]);

    $provider = Mockery::mock(AbstractProvider::class);
    $provider->shouldReceive('stateless')->andReturnSelf();
    $provider->shouldReceive('user')->andReturn($googleUser);
    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $state = 'oauth-state-token';
    Cache::put('google_oauth_return:'.hash('sha256', $state), 'http://localhost:3002', now()->addMinutes(10));

    $response = $this->withUnencryptedCookie('oauth_state', $state)
        ->get('/api/auth/google/callback?code=fake-code&state='.$state);

    $response->assertRedirect();
    expect($response->headers->get('Location'))->toStartWith('http://localhost:3002/auth/callback?token=');
});

it('redirects to the frontend with an error when google is disabled', function (): void {
    $response = $this->get('/api/auth/google/redirect');

    $response->assertRedirect();
    expect($response->headers->get('Location'))->toContain('error=google_disabled');
});

it('encrypts the stored google client secret', function (): void {
    $settings = enableGoogleLogin();

    expect($settings->getAttributes()['google_client_secret'])->not->toBe('super-secret');
    expect($settings->resolvedGoogleClientSecret())->toBe('super-secret');
    expect($settings->googleEnabled())->toBeTrue();
});

it('invalidates the password when google claims an unverified email', function (): void {
    Mail::fake();
    enableGoogleLogin();

    $client = Client::factory()->unverified()->create([
        'email' => 'taken@example.com',
        'password' => 'attacker-password',
    ]);
    $client->createToken('spa');
    $attackerTokenId = $client->tokens()->firstOrFail()->id;

    $googleUser = new User;
    $googleUser->map([
        'id' => 'google-owner',
        'name' => 'Dueño Real',
        'email' => 'taken@example.com',
        'avatar' => null,
    ]);

    $provider = Mockery::mock(AbstractProvider::class);
    $provider->shouldReceive('stateless')->andReturnSelf();
    $provider->shouldReceive('user')->andReturn($googleUser);
    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $state = 'state-for-unverified-takeover';
    Cache::put('google_oauth_return:'.hash('sha256', $state), 'http://localhost', now()->addMinutes(10));

    $this->withUnencryptedCookie('oauth_state', $state)
        ->get('/api/auth/google/callback?code=fake-code&state='.$state)
        ->assertRedirect();

    $client->refresh();
    expect($client->google_id)->toBe('google-owner');
    expect($client->email_verified_at)->not->toBeNull();
    expect($client->password)->toBeNull();
    expect($client->tokens()->whereKey($attackerTokenId)->exists())->toBeFalse();
    expect($client->tokens()->count())->toBe(1);
    expect($client->tokens()->first()->name)->toBe('google');
    Mail::assertNothingOutgoing();

    $this->postJson('/api/auth/login', [
        'email' => 'taken@example.com',
        'password' => 'attacker-password',
    ])->assertUnprocessable()->assertJsonValidationErrors(['email']);
});

it('keeps the password when google links an already verified account', function (): void {
    enableGoogleLogin();

    $client = Client::factory()->create([
        'email' => 'verified@example.com',
        'password' => 'owner-password',
    ]);

    $googleUser = new User;
    $googleUser->map([
        'id' => 'google-verified',
        'name' => 'Verified Client',
        'email' => 'verified@example.com',
        'avatar' => null,
    ]);

    $provider = Mockery::mock(AbstractProvider::class);
    $provider->shouldReceive('stateless')->andReturnSelf();
    $provider->shouldReceive('user')->andReturn($googleUser);
    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $state = 'state-for-verified-link';
    Cache::put('google_oauth_return:'.hash('sha256', $state), 'http://localhost', now()->addMinutes(10));

    $this->withUnencryptedCookie('oauth_state', $state)
        ->get('/api/auth/google/callback?code=fake-code&state='.$state)
        ->assertRedirect();

    $client->refresh();
    expect($client->google_id)->toBe('google-verified');
    expect(Hash::check('owner-password', $client->password))->toBeTrue();
});
