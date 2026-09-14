<?php

namespace App\Services;

use App\Mail\ClientVerificationCodeMail;
use App\Models\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class EmailVerificationService
{
    public const TTL_MINUTES = 15;

    public const MAX_ATTEMPTS = 5;

    public const RESEND_SECONDS = 60;

    public const RESEND_HOURLY_MAX = 5;

    public function send(Client $client): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put($this->codeKey($client), [
            'hash' => Hash::make($code),
            'attempts' => 0,
        ], now()->addMinutes(self::TTL_MINUTES));

        Mail::to($client->email)->send(new ClientVerificationCodeMail($client, $code));

        RateLimiter::hit($this->resendCooldownKey($client->email), self::RESEND_SECONDS);
        RateLimiter::hit($this->resendHourlyKey($client->email), 3600);
    }

    public function verify(Client $client, string $code): bool
    {
        $payload = Cache::get($this->codeKey($client));

        if (! is_array($payload) || ! is_string($payload['hash'] ?? null)) {
            return false;
        }

        $attempts = (int) ($payload['attempts'] ?? 0);

        if ($attempts >= self::MAX_ATTEMPTS) {
            Cache::forget($this->codeKey($client));

            return false;
        }

        if (! Hash::check($code, $payload['hash'])) {
            $payload['attempts'] = $attempts + 1;

            if ($payload['attempts'] >= self::MAX_ATTEMPTS) {
                Cache::forget($this->codeKey($client));
            } else {
                Cache::put($this->codeKey($client), $payload, now()->addMinutes(self::TTL_MINUTES));
            }

            return false;
        }

        Cache::forget($this->codeKey($client));

        return true;
    }

    public function resend(string $email): void
    {
        $this->assertCanResend($email);

        $client = Client::query()->where('email', $email)->first();

        if ($client === null || $client->hasVerifiedEmail()) {
            RateLimiter::hit($this->resendCooldownKey($email), self::RESEND_SECONDS);

            return;
        }

        $this->send($client);
    }

    private function assertCanResend(string $email): void
    {
        if (RateLimiter::tooManyAttempts($this->resendCooldownKey($email), 1)
            || RateLimiter::tooManyAttempts($this->resendHourlyKey($email), self::RESEND_HOURLY_MAX)) {
            throw ValidationException::withMessages([
                'email' => 'Espera un momento antes de solicitar otro código.',
            ]);
        }
    }

    private function codeKey(Client $client): string
    {
        return 'client-email-verify:'.$client->getKey();
    }

    private function resendCooldownKey(string $email): string
    {
        return 'client-email-verify-resend:'.strtolower($email);
    }

    private function resendHourlyKey(string $email): string
    {
        return 'client-email-verify-hourly:'.strtolower($email);
    }
}
