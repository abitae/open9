<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class PaymentSetting extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate(['id' => 1], [
            'provider' => 'mercadopago',
            'is_enabled' => false,
            'mode' => 'sandbox',
            'currency' => 'PEN',
        ]);
    }

    public function isSandbox(): bool
    {
        return ($this->mode ?? 'sandbox') !== 'production';
    }

    public function resolvedAccessToken(): ?string
    {
        return $this->decryptValue($this->isSandbox() ? $this->sandbox_access_token : $this->access_token);
    }

    public function resolvedPublicKey(): ?string
    {
        return $this->isSandbox() ? $this->sandbox_public_key : $this->public_key;
    }

    public function resolvedWebhookSecret(): ?string
    {
        return $this->decryptValue($this->webhook_secret);
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
