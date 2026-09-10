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
        return (bool) $this->google_enabled
            && ! empty($this->google_client_id)
            && $this->resolvedGoogleClientSecret() !== null;
    }

    public function resolvedGoogleClientSecret(): ?string
    {
        return $this->decryptValue($this->google_client_secret);
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
