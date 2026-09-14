<?php

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
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
        /** @var Client $client */
        $client = $request->user();

        $order = $client->orders()
            ->with('items')
            ->where('order_code', $orderCode)
            ->first();

        if ($order === null) {
            return response()->json(['message' => 'Pedido no encontrado.'], 404);
        }

        return response()->json(['order' => $this->detail($order)]);
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
