<?php

namespace App\Services;

use App\Enums\PublishStatus;
use App\Mail\OrderConfirmationMail;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(
        private readonly UniqueCodeService $codes,
    ) {}

    /**
     * Crea una orden en estado pendiente. Los precios y el total SIEMPRE se
     * recalculan desde la base de datos en la moneda de cobro; nunca se
     * confía en montos enviados por el cliente.
     *
     * @param  array{name: string, email: string, phone?: string|null, notes?: string|null}  $buyer
     * @param  array<int, array{product_id: int, quantity: int}>  $items
     * @param  array<string, mixed>|null  $shippingAddress
     */
    public function create(array $buyer, array $items, string $currency = 'PEN', ?Client $client = null, ?array $shippingAddress = null): Order
    {
        $currency = strtoupper($currency);
        $lines = $this->resolveLines($items, $currency);

        return DB::transaction(function () use ($buyer, $lines, $currency, $client, $shippingAddress): Order {
            $order = Order::query()->create([
                'client_id' => $client?->getKey(),
                'order_code' => $this->codes->make(Order::class, 'order_code', 'ORD'),
                'buyer_name' => $buyer['name'],
                'buyer_email' => $buyer['email'],
                'buyer_phone' => $buyer['phone'] ?? null,
                'total' => round(collect($lines)->sum('subtotal'), 2),
                'currency' => $currency,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'shipping_address' => $shippingAddress,
                'notes' => $buyer['notes'] ?? null,
            ]);

            foreach ($lines as $line) {
                $order->items()->create([
                    'product_id' => $line['product']->id,
                    'product_name' => $line['product']->name,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'subtotal' => $line['subtotal'],
                ]);
            }

            return $order->fresh('items');
        });
    }

    /**
     * Confirma el pago de una orden. Es idempotente: si ya está pagada no
     * vuelve a descontar stock ni a notificar. Devuelve true solo cuando
     * la orden transiciona a pagada en esta llamada.
     */
    public function markAsPaid(Order $order, ?string $paymentId = null): bool
    {
        return DB::transaction(function () use ($order, $paymentId): bool {
            /** @var Order $locked */
            $locked = Order::query()->lockForUpdate()->findOrFail($order->getKey());

            if ($locked->payment_status === 'paid') {
                return false;
            }

            $locked->loadMissing('items');

            foreach ($locked->items as $item) {
                if ($item->product_id === null) {
                    continue;
                }

                $product = Product::query()->lockForUpdate()->find($item->product_id);

                if ($product === null) {
                    continue;
                }

                $product->stock = max(0, (int) $product->stock - (int) $item->quantity);
                $product->save();
            }

            $locked->update([
                'payment_status' => 'paid',
                'status' => 'confirmed',
                'mercadopago_payment_id' => $paymentId ?? $locked->mercadopago_payment_id,
            ]);

            $this->sendConfirmation($locked->fresh('items'));

            return true;
        });
    }

    public function markAsFailed(Order $order): void
    {
        if ($order->payment_status === 'paid') {
            return;
        }

        $order->update(['payment_status' => 'failed']);
    }

    private function sendConfirmation(Order $order): void
    {
        try {
            Mail::to($order->buyer_email)->send(new OrderConfirmationMail($order));
        } catch (\Throwable $exception) {
            Log::warning('No se pudo enviar el correo de confirmación de la orden '.$order->order_code, [
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * @param  array<int, array{product_id: int, quantity: int}>  $items
     * @return array<int, array{product: Product, quantity: int, unit_price: float, subtotal: float}>
     */
    private function resolveLines(array $items, string $currency): array
    {
        if ($items === []) {
            throw ValidationException::withMessages([
                'items' => 'Tu carrito está vacío.',
            ]);
        }

        $lines = [];

        foreach ($items as $item) {
            $product = Product::query()
                ->where('status', PublishStatus::Published)
                ->find($item['product_id']);

            if ($product === null) {
                throw ValidationException::withMessages([
                    'items' => 'Uno de los productos ya no está disponible.',
                ]);
            }

            $quantity = max(1, (int) $item['quantity']);

            if ($product->stock !== null && $product->stock < $quantity) {
                throw ValidationException::withMessages([
                    'items' => "No hay stock suficiente de {$product->name}.",
                ]);
            }

            $unitPrice = $this->priceInCurrency($product, $currency);

            $lines[] = [
                'product' => $product,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => round($unitPrice * $quantity, 2),
            ];
        }

        return $lines;
    }

    private function priceInCurrency(Product $product, string $currency): float
    {
        $price = (float) $product->price;
        $base = strtoupper($product->currency ?: 'USD');

        if ($base === $currency) {
            return round($price, 2);
        }

        $rate = $this->usdPenRate();

        if ($base === 'USD' && $currency === 'PEN') {
            return round($price * $rate, 2);
        }

        if ($base === 'PEN' && $currency === 'USD') {
            return round($price / $rate, 2);
        }

        return round($price, 2);
    }

    private function usdPenRate(): float
    {
        $setting = Setting::query()
            ->where('group', 'store')
            ->where('key', 'usd_pen_rate')
            ->value('value');

        $rate = is_numeric($setting) ? (float) $setting : 3.75;

        return max(0.01, $rate);
    }
}
