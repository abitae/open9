<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SocialLoginSetting extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'google_enabled' => 'boolean',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate(['id' => 1], [
            'google_enabled' => false,
        ]);
    }

    public function googleEnabled(): bool
    {
        if ($this->resolvedGoogleClientId() === null || $this->resolvedGoogleClientSecret() === null) {
            return false;
        }

        return (bool) $this->google_enabled || $this->hasEnvGoogleCredentials();
    }

    public function resolvedGoogleClientId(): ?string
    {
        if (is_string($this->google_client_id) && $this->google_client_id !== '') {
            return $this->google_client_id;
        }

        $fromEnv = config('services.google.client_id');

        return is_string($fromEnv) && $fromEnv !== '' ? $fromEnv : null;
    }

    public function resolvedGoogleClientSecret(): ?string
    {
        $fromDatabase = $this->decryptValue($this->google_client_secret);

        if ($fromDatabase !== null) {
            return $fromDatabase;
        }

        $fromEnv = config('services.google.client_secret');

        return is_string($fromEnv) && $fromEnv !== '' ? $fromEnv : null;
    }

    private function hasEnvGoogleCredentials(): bool
    {
        $id = config('services.google.client_id');
        $secret = config('services.google.client_secret');

        return is_string($id) && $id !== '' && is_string($secret) && $secret !== '';
    }

    private function decryptValue(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException) {
            return null;
        }
    }
}
