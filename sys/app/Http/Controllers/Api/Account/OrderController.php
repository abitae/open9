<?php

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Order;
use App\Services\MercadoPagoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private readonly MercadoPagoService $mercadopago,
    ) {}

    public function index(Request $request): JsonResponse
    {
        /** @var Client $client */
        $client = $request->user();

        $orders = $client->orders()
            ->withCount('items')
            ->orderByDesc('id')
            ->paginate(10);

        return response()->json([
            'data' => collect($orders->items())
                ->map(fn (Order $order): array => $this->summary($order))
                ->all(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function show(Request $request, string $orderCode): JsonResponse
    {
        $order = $this->ownedOrder($request, $orderCode, withItems: true);

        if ($order === null) {
            return response()->json(['message' => 'Pedido no encontrado.'], 404);
        }

        return response()->json(['order' => $this->detail($order)]);
    }

    public function pay(Request $request, string $orderCode): JsonResponse
    {
        $order = $this->ownedOrder($request, $orderCode, withItems: true);

        if ($order === null) {
            return response()->json(['message' => 'Pedido no encontrado.'], 404);
        }

        if (! $order->canPay()) {
            return response()->json([
                'message' => 'Este pedido ya no se puede pagar.',
            ], 422);
        }

        if (! $this->mercadopago->isEnabled()) {
            return response()->json([
                'message' => 'Los pagos en línea no están disponibles por el momento. Inténtalo más tarde.',
            ], 503);
        }

        $order->reopenForPayment();

        $initPoint = null;
        $preferenceId = $order->mercadopago_preference_id;

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
            'buyer_email' => $order->buyer_email,
            'init_point' => $initPoint,
            'preference_id' => $preferenceId,
            'public_key' => $this->mercadopago->publicKey(),
        ]);
    }

    private function ownedOrder(Request $request, string $orderCode, bool $withItems = false): ?Order
    {
        /** @var Client $client */
        $client = $request->user();

        $query = $client->orders()->where('order_code', $orderCode);

        if ($withItems) {
            $query->with('items');
        }

        return $query->first();
    }

    /**
     * @return array<string, mixed>
     */
    private function summary(Order $order): array
    {
        return [
            'order_code' => $order->order_code,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'can_pay' => $order->canPay(),
            'total' => (float) $order->total,
            'currency' => $order->currency,
            'items_count' => (int) ($order->items_count ?? 0),
            'created_at' => $order->created_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function detail(Order $order): array
    {
        return [
            'order_code' => $order->order_code,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'can_pay' => $order->canPay(),
            'total' => (float) $order->total,
            'currency' => $order->currency,
            'buyer_name' => $order->buyer_name,
            'buyer_email' => $order->buyer_email,
            'buyer_phone' => $order->buyer_phone,
            'shipping_address' => $order->shipping_address,
            'notes' => $order->notes,
            'created_at' => $order->created_at?->toIso8601String(),
            'items' => $order->items->map(fn ($item): array => [
                'product_name' => $item->product_name,
                'quantity' => (int) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'subtotal' => (float) $item->subtotal,
            ])->all(),
        ];
    }
}
