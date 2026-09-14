<?php

use App\Models\Client;
use App\Models\SocialLoginSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\User as SocialiteUser;

uses(RefreshDatabase::class);

function enableGoogleLoginForOauthSecurityTest(): void
{
    SocialLoginSetting::query()->updateOrCreate(['id' => 1], [
        'google_enabled' => true,
        'google_client_id' => 'client-id.apps.googleusercontent.com',
        'google_client_secret' => Crypt::encryptString('super-secret'),
        'google_redirect_url' => 'http://localhost/api/auth/google/callback',
    ]);
}

function mockGoogleProviderForOauthSecurityTest(string $googleId = 'google-1', string $email = 'oauth@example.com'): void
{
    $googleUser = new SocialiteUser;
    $googleUser->map(['id' => $googleId, 'name' => 'OAuth Client', 'email' => $email, 'avatar' => null]);

    $provider = Mockery::mock(AbstractProvider::class);
    $provider->shouldReceive('stateless')->andReturnSelf();
    $provider->shouldReceive('user')->andReturn($googleUser);
    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
}

function seedValidOauthState(string $state, string $returnTo = 'http://localhost:3002'): void
{
    Cache::put('google_oauth_return:'.hash('sha256', $state), $returnTo, now()->addMinutes(10));
}

it('stores APP_URL as oauth return_to when FRONTEND_URL is a local origin', function (): void {
    enableGoogleLoginForOauthSecurityTest();
    config([
        'app.url' => 'https://open9.test',
        'app.frontend_url' => 'http://localhost:3002',
    ]);

    $capturedState = null;
    $provider = Mockery::mock(AbstractProvider::class);
    $provider->shouldReceive('stateless')->andReturnSelf();
    $provider->shouldReceive('with')->andReturnUsing(function (array $params) use (&$capturedState, $provider) {
        $capturedState = $params['state'] ?? null;

        return $provider;
    });
    $provider->shouldReceive('redirect')->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));
    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $response = $this->get('/api/auth/google/redirect?return_to='.urlencode('https://open9.test'));

    $response->assertRedirect();
    expect($capturedState)->toBeString()->not->toBeEmpty();
    expect(Cache::get('google_oauth_return:'.hash('sha256', (string) $capturedState)))->toBe('https://open9.test');
});

it('sends the user back to login when google denies the grant', function (): void {
    enableGoogleLoginForOauthSecurityTest();

    $state = 'state-for-denied-grant';
    seedValidOauthState($state, 'http://localhost:3002');

    $response = $this->withUnencryptedCookie('oauth_state', $state)
        ->get('/api/auth/google/callback?error=access_denied&state='.$state);

    $response->assertRedirect();
    expect($response->headers->get('Location'))->toContain('error=google_denied');
    $this->assertDatabaseCount('clients', 0);
});

it('rejects the google callback when no state is provided at all', function (): void {
    enableGoogleLoginForOauthSecurityTest();
    mockGoogleProviderForOauthSecurityTest();

    $response = $this->get('/api/auth/google/callback?code=fake-code');

    $response->assertRedirect();
    expect($response->headers->get('Location'))->toContain('error=google_state_invalid');
    $this->assertDatabaseCount('clients', 0);
});

it('rejects the google callback when the browser has no oauth_state cookie', function (): void {
    enableGoogleLoginForOauthSecurityTest();
    mockGoogleProviderForOauthSecurityTest();

    $state = 'state-without-cookie';
    seedValidOauthState($state);

    $response = $this->get('/api/auth/google/callback?code=fake-code&state='.$state);

    $response->assertRedirect();
    expect($response->headers->get('Location'))->toContain('error=google_state_invalid');
    $this->assertDatabaseCount('clients', 0);
});

it('rejects the google callback when the state does not match the initiating browser cookie', function (): void {
    enableGoogleLoginForOauthSecurityTest();
    mockGoogleProviderForOauthSecurityTest();

    $attackerState = 'state-from-attacker-flow';
    seedValidOauthState($attackerState);

    $response = $this->withUnencryptedCookie('oauth_state', 'a-completely-different-state')
        ->get('/api/auth/google/callback?code=fake-code&state='.$attackerState);

    $response->assertRedirect();
    expect($response->headers->get('Location'))->toContain('error=google_state_invalid');
    $this->assertDatabaseCount('clients', 0);
});

it('rejects a state that matches the cookie but is unknown or expired', function (): void {
    enableGoogleLoginForOauthSecurityTest();
    mockGoogleProviderForOauthSecurityTest();

    $state = 'never-cached-or-already-expired';

    $response = $this->withUnencryptedCookie('oauth_state', $state)
        ->get('/api/auth/google/callback?code=fake-code&state='.$state);

    $response->assertRedirect();
    expect($response->headers->get('Location'))->toContain('error=google_state_invalid');
    $this->assertDatabaseCount('clients', 0);
});

it('accepts a matching state and cookie exactly once', function (): void {
    enableGoogleLoginForOauthSecurityTest();
    mockGoogleProviderForOauthSecurityTest(email: 'once@example.com');

    $state = 'single-use-state';
    seedValidOauthState($state, 'http://localhost:3002');

    $first = $this->withUnencryptedCookie('oauth_state', $state)
        ->get('/api/auth/google/callback?code=fake-code&state='.$state);

    $first->assertRedirect();
    expect($first->headers->get('Location'))->toStartWith('http://localhost:3002/auth/callback?token=');
    $this->assertDatabaseHas('clients', ['email' => 'once@example.com']);

    // Reintentar el mismo state (p. ej. recargando el callback) ya no es válido.
    $second = $this->withUnencryptedCookie('oauth_state', $state)
        ->get('/api/auth/google/callback?code=fake-code&state='.$state);

    $second->assertRedirect();
    expect($second->headers->get('Location'))->toContain('error=google_state_invalid');
});

it('blocks an existing suspended client from logging in through google', function (): void {
    enableGoogleLoginForOauthSecurityTest();

    $client = Client::factory()->create([
        'email' => 'suspended-oauth@example.com',
        'google_id' => 'google-suspended',
        'status' => 'suspended',
    ]);

    mockGoogleProviderForOauthSecurityTest(googleId: 'google-suspended', email: 'suspended-oauth@example.com');

    $state = 'state-for-suspended-client';
    seedValidOauthState($state);

    $response = $this->withUnencryptedCookie('oauth_state', $state)
        ->get('/api/auth/google/callback?code=fake-code&state='.$state);

    $response->assertRedirect();
    expect($response->headers->get('Location'))->toContain('error=account_suspended');
    expect($client->tokens()->count())->toBe(0);
});

it('revokes a client tokens automatically as soon as it is suspended', function (): void {
    $client = Client::factory()->create(['status' => 'active']);
    $token = $client->createToken('spa')->plainTextToken;

    $client->update(['status' => 'suspended']);

    expect($client->tokens()->count())->toBe(0);

    $this->app['auth']->forgetGuards();
    $this->withToken($token)->getJson('/api/auth/me')->assertStatus(401);
});

it('blocks account access and revokes the token in use if a suspension slips past the model event', function (): void {
    $client = Client::factory()->create(['status' => 'active']);

    // Simula un camino que cambie el estado sin disparar el evento del modelo
    // (defensa en profundidad: el middleware no debe depender solo del observer).
    Client::withoutEvents(function () use ($client): void {
        $client->update(['status' => 'suspended']);
    });

    $token = $client->createToken('spa')->plainTextToken;

    $this->withToken($token)->getJson('/api/auth/me')->assertStatus(403);
    expect($client->fresh()->tokens()->count())->toBe(0);
});
