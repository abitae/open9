<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'shipping_address' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return HasMany<OrderPayment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(OrderPayment::class);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function canPay(): bool
    {
        if ($this->isPaid() || in_array($this->status, ['confirmed', 'completed'], true)) {
            return false;
        }

        return in_array($this->payment_status, ['unpaid', 'failed'], true);
    }

    public function reopenForPayment(): void
    {
        if ($this->status === 'cancelled') {
            $this->status = 'pending';
        }

        if ($this->payment_status === 'failed') {
            $this->payment_status = 'unpaid';
        }

        if ($this->isDirty()) {
            $this->save();
        }
    }
}
