<?php

use App\Enums\RecordStatus;
use App\Mail\ClientGooglePasswordHintMail;
use App\Mail\ClientPasswordResetMail;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

function queuedPasswordResetToken(string $email): string
{
    $token = '';

    Mail::assertQueued(ClientPasswordResetMail::class, function (ClientPasswordResetMail $mail) use ($email, &$token): bool {
        if (! $mail->hasTo($email)) {
            return false;
        }

        $token = $mail->token;

        return true;
    });

    expect($token)->not->toBeEmpty();

    return $token;
}

it('queues a reset mail for an active client with a password', function (): void {
    Mail::fake();

    $client = Client::factory()->create([
        'email' => 'ada@example.com',
        'password' => 'old-password',
    ]);

    $this->postJson('/api/auth/forgot-password', [
        'email' => $client->email,
    ])->assertSuccessful()
        ->assertJsonPath('message', 'Si el correo está registrado, te enviamos instrucciones.');

    queuedPasswordResetToken('ada@example.com');
    Mail::assertQueued(ClientPasswordResetMail::class, function (ClientPasswordResetMail $mail) use ($client): bool {
        return $mail->hasTo($client->email)
            && str_contains($mail->resetUrl(), '/restablecer-contrasena?')
            && str_contains($mail->resetUrl(), 'token=')
            && str_contains($mail->resetUrl(), rawurlencode($client->email));
    });
    Mail::assertNotQueued(ClientGooglePasswordHintMail::class);
});

it('returns the same message and sends no mail for an unknown email', function (): void {
    Mail::fake();

    $this->postJson('/api/auth/forgot-password', [
        'email' => 'nobody@example.com',
    ])->assertSuccessful()
        ->assertJsonPath('message', 'Si el correo está registrado, te enviamos instrucciones.');

    Mail::assertNothingQueued();
});

it('does not email an inactive client', function (): void {
    Mail::fake();

    Client::factory()->create([
        'email' => 'paused@example.com',
        'password' => 'old-password',
        'status' => RecordStatus::Inactive,
    ]);

    $this->postJson('/api/auth/forgot-password', [
        'email' => 'paused@example.com',
    ])->assertSuccessful()
        ->assertJsonPath('message', 'Si el correo está registrado, te enviamos instrucciones.');

    Mail::assertNothingQueued();
});

it('emails a google-only client a sign-in hint instead of a reset link', function (): void {
    Mail::fake();

    Client::factory()->google()->create([
        'email' => 'google@example.com',
    ]);

    $this->postJson('/api/auth/forgot-password', [
        'email' => 'google@example.com',
    ])->assertSuccessful()
        ->assertJsonPath('message', 'Si el correo está registrado, te enviamos instrucciones.');

    Mail::assertQueued(ClientGooglePasswordHintMail::class, function (ClientGooglePasswordHintMail $mail): bool {
        return $mail->hasTo('google@example.com');
    });
    Mail::assertNotQueued(ClientPasswordResetMail::class);
});

it('resets the password with a valid token and does not issue a session token', function (): void {
    Mail::fake();

    $client = Client::factory()->create([
        'email' => 'ada@example.com',
        'password' => 'old-password',
    ]);
    $client->createToken('spa');

    $this->postJson('/api/auth/forgot-password', [
        'email' => 'ada@example.com',
    ])->assertSuccessful();

    $token = queuedPasswordResetToken('ada@example.com');

    $response = $this->postJson('/api/auth/reset-password', [
        'email' => 'ada@example.com',
        'token' => $token,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('message', 'Tu contraseña se actualizó. Ya puedes ingresar.');

    expect($response->json('token'))->toBeNull();

    $client->refresh();
    expect(Hash::check('new-password', $client->password))->toBeTrue();
    expect(Hash::check('old-password', $client->password))->toBeFalse();
    expect($client->tokens()->count())->toBe(0);
});

it('verifies the email when an unverified client resets the password', function (): void {
    Mail::fake();

    $client = Client::factory()->unverified()->create([
        'email' => 'unverified@example.com',
        'password' => 'old-password',
    ]);

    $this->postJson('/api/auth/forgot-password', [
        'email' => $client->email,
    ])->assertSuccessful();

    $token = queuedPasswordResetToken($client->email);

    $this->postJson('/api/auth/reset-password', [
        'email' => $client->email,
        'token' => $token,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])->assertSuccessful();

    expect($client->fresh()->hasVerifiedEmail())->toBeTrue();
});

it('allows login with the new password after a reset', function (): void {
    Mail::fake();

    Client::factory()->create([
        'email' => 'ada@example.com',
        'password' => 'old-password',
    ]);

    $this->postJson('/api/auth/forgot-password', [
        'email' => 'ada@example.com',
    ])->assertSuccessful();

    $token = queuedPasswordResetToken('ada@example.com');

    $this->postJson('/api/auth/reset-password', [
        'email' => 'ada@example.com',
        'token' => $token,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])->assertSuccessful();

    $this->postJson('/api/auth/login', [
        'email' => 'ada@example.com',
        'password' => 'old-password',
    ])->assertUnprocessable();

    $this->postJson('/api/auth/login', [
        'email' => 'ada@example.com',
        'password' => 'new-password',
    ])->assertSuccessful()
        ->assertJsonStructure(['token', 'client']);
});

it('rejects an invalid reset token', function (): void {
    Mail::fake();

    Client::factory()->create([
        'email' => 'ada@example.com',
        'password' => 'old-password',
    ]);

    $this->postJson('/api/auth/reset-password', [
        'email' => 'ada@example.com',
        'token' => 'not-a-real-token',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('rejects an expired reset token', function (): void {
    Mail::fake();

    Client::factory()->create([
        'email' => 'ada@example.com',
        'password' => 'old-password',
    ]);

    $this->postJson('/api/auth/forgot-password', [
        'email' => 'ada@example.com',
    ])->assertSuccessful();

    $token = queuedPasswordResetToken('ada@example.com');

    $this->travel(61)->minutes();

    $this->postJson('/api/auth/reset-password', [
        'email' => 'ada@example.com',
        'token' => $token,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});
