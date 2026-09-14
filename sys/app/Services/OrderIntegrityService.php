<?php

namespace App\Services;

use App\Models\Order;

class OrderIntegrityService
{
    public function fingerprint(Order $order): string
    {
        $order->loadMissing('items');

        $items = $order->items
            ->sortBy(fn ($item): int => (int) $item->product_id)
            ->values()
            ->map(fn ($item): array => [
                'product_id' => (int) $item->product_id,
                'quantity' => (int) $item->quantity,
                'unit_price' => number_format((float) $item->unit_price, 2, '.', ''),
                'subtotal' => number_format((float) $item->subtotal, 2, '.', ''),
            ])
            ->all();

        $payload = [
            'order_code' => $order->order_code,
            'buyer_email' => $order->buyer_email,
            'buyer_name' => $order->buyer_name,
            'total' => number_format((float) $order->total, 2, '.', ''),
            'currency' => $order->currency,
            'items' => $items,
        ];

        return hash_hmac(
            'sha256',
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            (string) config('app.key'),
        );
    }

    public function assign(Order $order): void
    {
        $order->forceFill([
            'integrity_hash' => $this->fingerprint($order),
        ])->save();
    }

    public function verify(Order $order): bool
    {
        if ($order->integrity_hash === null || $order->integrity_hash === '') {
            $this->assign($order);

            return true;
        }

        return hash_equals($order->integrity_hash, $this->fingerprint($order));
    }
}
