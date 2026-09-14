<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Order;
use App\Services\MercadoPagoService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly OrderService $orders,
        private readonly MercadoPagoService $mercadopago,
    ) {}

    /**
     * Crea una orden pendiente e inicia el pago con MercadoPago.
     * La orden solo se confirma cuando el webhook reporta el pago aprobado.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'buyer.name' => ['required', 'string', 'max:255'],
            'buyer.email' => ['required', 'email', 'max:255'],
            'buyer.phone' => ['nullable', 'string', 'max:255'],
            'buyer.notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'shipping_address' => ['nullable', 'array'],
            'shipping_address.recipient_name' => ['nullable', 'string', 'max:255'],
            'shipping_address.phone' => ['nullable', 'string', 'max:255'],
            'shipping_address.line1' => ['nullable', 'string', 'max:255'],
            'shipping_address.line2' => ['nullable', 'string', 'max:255'],
            'shipping_address.city' => ['nullable', 'string', 'max:255'],
            'shipping_address.region' => ['nullable', 'string', 'max:255'],
            'shipping_address.country' => ['nullable', 'string', 'max:2'],
            'shipping_address.postal_code' => ['nullable', 'string', 'max:20'],
        ]);

        if (! $this->mercadopago->isEnabled()) {
            return response()->json([
                'message' => 'Los pagos en línea no están disponibles por el momento. Inténtalo más tarde.',
            ], 503);
        }

        /** @var Client|null $client */
        $client = $request->user('sanctum');

        $order = $this->orders->create(
            $data['buyer'],
            $data['items'],
            $this->mercadopago->currency(),
            $client,
            $data['shipping_address'] ?? null,
        );

        // La preferencia habilita el pago con billetera Mercado Pago y el
        // init_point de respaldo. Si falla (p. ej. back_urls no públicas en
        // desarrollo) NO abortamos: el Payment Brick sigue cobrando con
        // tarjeta/Yape usando solo la public_key.
        $initPoint = null;
        $preferenceId = null;

        try {
            $preference = $this->mercadopago->createPreference($order);
            $initPoint = $preference['init_point'];
            $preferenceId = $preference['preference_id'];
        } catch (\Throwable $exception) {
            report($exception);
        }

        return response()->json([
            'order_code' => $order->order_code,
            'total' => (float) $order->total,
            'currency' => $order->currency,
            'init_point' => $initPoint,
            'preference_id' => $preferenceId,
            'public_key' => $this->mercadopago->publicKey(),
        ], 201);
    }

    /**
     * Procesa el pago tokenizado por Checkout Bricks (Payment Brick) sobre una
     * orden ya creada. La orden solo se confirma si el pago queda aprobado.
     */
    public function process(Request $request): JsonResponse
    {
        $data = $request->validate([
            'order_code' => ['required', 'string', 'exists:orders,order_code'],
            'form_data' => ['required', 'array'],
        ]);

        if (! $this->mercadopago->isEnabled()) {
            return response()->json([
                'message' => 'Los pagos en línea no están disponibles por el momento.',
            ], 503);
        }

        /** @var Order $order */
        $order = Order::query()->where('order_code', $data['order_code'])->firstOrFail();

        if ($order->payment_status === 'paid') {
            return response()->json([
                'order_code' => $order->order_code,
                'status' => 'approved',
                'status_detail' => 'accredited',
                'payment_status' => $order->payment_status,
            ]);
        }

        try {
            $result = $this->mercadopago->createPayment($order, $data['form_data']);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'No se pudo procesar el pago. Inténtalo nuevamente.',
            ], 502);
        }

        $order->refresh();

        return response()->json([
            'order_code' => $order->order_code,
            'status' => $result['status'],
            'status_detail' => $result['status_detail'],
            'payment_status' => $order->payment_status,
        ]);
    }

    public function show(string $orderCode): JsonResponse
    {
        $order = Order::query()
            ->with('items')
            ->where('order_code', $orderCode)
            ->first();

        if ($order === null) {
            return response()->json(['message' => 'Pedido no encontrado.'], 404);
        }

        return response()->json([
            'order_code' => $order->order_code,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'total' => (float) $order->total,
            'currency' => $order->currency,
            'buyer_name' => $order->buyer_name,
            'items' => $order->items->map(fn ($item): array => [
                'product_name' => $item->product_name,
                'quantity' => (int) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'subtotal' => (float) $item->subtotal,
            ])->all(),
        ]);
    }

    public function webhook(Request $request): JsonResponse
    {
        $this->mercadopago->handleWebhook($request);

        return response()->json(['received' => true]);
    }
}
