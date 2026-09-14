<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\PaymentSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MercadoPagoService
{
    public function __construct(
        private readonly OrderService $orders,
    ) {}

    public function settings(): PaymentSetting
    {
        return PaymentSetting::current();
    }

    public function isEnabled(): bool
    {
        $settings = $this->settings();

        return (bool) $settings->is_enabled && $this->accessToken($settings) !== null;
    }

    public function currency(): string
    {
        return strtoupper($this->settings()->currency ?: 'PEN');
    }

    public function publicKey(): ?string
    {
        return $this->settings()->resolvedPublicKey();
    }

    /**
     * Crea la preferencia de MercadoPago para una orden ya existente. En el
     * flujo de Checkout Bricks la usa el Payment Brick para habilitar el pago
     * con billetera Mercado Pago; también deja disponible un init_point de
     * respaldo por si se quisiera redirigir.
     *
     * @return array{init_point: string, preference_id: string}
     */
    public function createPreference(Order $order): array
    {
        $settings = $this->settings();
        $token = $this->requireAccessToken($settings);
        $order->loadMissing('items');

        $successUrl = $this->resultUrl($order, 'success');
        $webhookUrl = url('/api/webhooks/mercadopago');

        $payload = [
            'items' => $order->items->map(fn ($item): array => [
                'id' => (string) $item->product_id,
                'title' => $item->product_name,
                'quantity' => (int) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'currency_id' => $order->currency,
            ])->all(),
            'payer' => [
                'name' => $order->buyer_name,
                'email' => $order->buyer_email,
            ],
            'external_reference' => $order->order_code,
            'statement_descriptor' => $settings->statement_descriptor ?: config('app.name'),
            'back_urls' => [
                'success' => $successUrl,
                'failure' => $this->resultUrl($order, 'failure'),
                'pending' => $this->resultUrl($order, 'pending'),
            ],
        ];

        // MercadoPago rechaza auto_return/notification_url con hosts locales
        // (localhost/127.0.0.1). Solo los enviamos cuando la URL es pública.
        if ($this->isPublicUrl($webhookUrl)) {
            $payload['notification_url'] = $webhookUrl;
        }

        if ($this->isPublicUrl($successUrl)) {
            $payload['auto_return'] = 'approved';
        }

        $preference = Http::withToken($token)
            ->acceptJson()
            ->post('https://api.mercadopago.com/checkout/preferences', $payload);

        if (! $preference->successful()) {
            Log::error('MercadoPago: no se pudo crear la preferencia.', [
                'order' => $order->order_code,
                'status' => $preference->status(),
                'body' => $preference->json(),
            ]);

            throw new \RuntimeException('No se pudo iniciar el pago con MercadoPago.');
        }

        $preferenceId = (string) $preference->json('id');

        $order->update(['mercadopago_preference_id' => $preferenceId]);

        OrderPayment::query()->updateOrCreate(
            ['order_id' => $order->id, 'provider_preference_id' => $preferenceId],
            [
                'provider' => 'mercadopago',
                'status' => 'pending',
                'amount' => (float) $order->total,
                'currency' => $order->currency,
            ],
        );

        $initPoint = $settings->isSandbox()
            ? ($preference->json('sandbox_init_point') ?? $preference->json('init_point'))
            : $preference->json('init_point');

        return [
            'init_point' => (string) $initPoint,
            'preference_id' => $preferenceId,
        ];
    }

    /**
     * Procesa un pago tokenizado en el navegador con Checkout Bricks
     * (Payment Brick) a través de la API de pagos. El monto SIEMPRE se toma
     * de la orden: nunca se confía en el importe enviado por el cliente.
     *
     * @param  array<string, mixed>  $formData  Datos que devuelve el Brick en onSubmit.
     * @return array{status: string, status_detail: ?string, payment_id: ?string}
     */
    public function createPayment(Order $order, array $formData): array
    {
        $settings = $this->settings();
        $token = $this->requireAccessToken($settings);

        $payer = is_array($formData['payer'] ?? null) ? $formData['payer'] : [];
        $payerEmail = is_string($payer['email'] ?? null) && $payer['email'] !== ''
            ? $payer['email']
            : $order->buyer_email;

        $payload = array_filter([
            'transaction_amount' => (float) $order->total,
            'description' => 'Pedido '.$order->order_code,
            'external_reference' => $order->order_code,
            'statement_descriptor' => $settings->statement_descriptor ?: config('app.name'),
            'token' => is_string($formData['token'] ?? null) ? $formData['token'] : null,
            'payment_method_id' => is_string($formData['payment_method_id'] ?? null) ? $formData['payment_method_id'] : null,
            'issuer_id' => $formData['issuer_id'] ?? null,
            'installments' => (int) ($formData['installments'] ?? 1),
            'payer' => array_filter([
                'email' => $payerEmail,
                'identification' => is_array($payer['identification'] ?? null) ? $payer['identification'] : null,
            ], static fn ($value): bool => $value !== null),
        ], static fn ($value): bool => $value !== null);

        // notification_url solo es válida con hosts públicos; en desarrollo
        // (localhost) se omite para no romper la creación del pago.
        $webhookUrl = url('/api/webhooks/mercadopago');
        if ($this->isPublicUrl($webhookUrl)) {
            $payload['notification_url'] = $webhookUrl;
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->withHeaders(['X-Idempotency-Key' => (string) Str::uuid()])
            ->post('https://api.mercadopago.com/v1/payments', $payload);

        if (! $response->successful()) {
            Log::error('MercadoPago: no se pudo crear el pago.', [
                'order' => $order->order_code,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            throw new \RuntimeException('No se pudo procesar el pago con MercadoPago.');
        }

        $paymentId = (string) $response->json('id');
        $status = (string) $response->json('status');

        $this->applyPaymentResult(
            $order,
            $paymentId,
            $status,
            (float) ($response->json('transaction_amount') ?? $order->total),
            is_string($response->json('currency_id')) ? $response->json('currency_id') : $order->currency,
            $response->json(),
        );

        return [
            'status' => $status,
            'status_detail' => is_string($response->json('status_detail')) ? $response->json('status_detail') : null,
            'payment_id' => $paymentId,
        ];
    }

    public function handleWebhook(Request $request): void
    {
        $settings = $this->settings();

        if (! $this->verifySignature($request, $settings)) {
            Log::warning('MercadoPago: firma de webhook inválida.', ['ip' => $request->ip()]);

            return;
        }

        $type = $request->input('type', $request->input('topic'));

        if ($type !== null && $type !== 'payment') {
            return;
        }

        $paymentId = $this->resolvePaymentId($request);

        if ($paymentId === null) {
            return;
        }

        $this->syncPayment((string) $paymentId);
    }

    private function resolvePaymentId(Request $request): ?string
    {
        $id = data_get($request->all(), 'data.id')
            ?? $request->input('data_id')
            ?? $request->query('data_id')
            ?? $request->input('id')
            ?? $request->query('id');

        return $id !== null ? (string) $id : null;
    }

    /**
     * Consulta el estado real del pago en MercadoPago y sincroniza la orden.
     * La orden solo se confirma cuando el pago está aprobado.
     */
    public function syncPayment(string $paymentId): ?Order
    {
        $settings = $this->settings();
        $token = $this->accessToken($settings);

        if ($token === null) {
            return null;
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->get("https://api.mercadopago.com/v1/payments/{$paymentId}");

        if (! $response->successful()) {
            Log::warning('MercadoPago: no se pudo consultar el pago.', [
                'payment_id' => $paymentId,
                'status' => $response->status(),
            ]);

            return null;
        }

        $orderCode = $response->json('external_reference');
        $status = (string) $response->json('status');
        $amount = (float) ($response->json('transaction_amount') ?? 0);

        $order = Order::query()->where('order_code', $orderCode)->first();

        if ($order === null) {
            return null;
        }

        $this->applyPaymentResult(
            $order,
            $paymentId,
            $status,
            $amount,
            is_string($response->json('currency_id')) ? $response->json('currency_id') : $order->currency,
            $response->json(),
        );

        return $order->fresh('items');
    }

    /**
     * Persiste el pago y sincroniza el estado de la orden. Solo se confirma
     * (y se descuenta stock) cuando MercadoPago reporta el pago aprobado.
     *
     * @param  array<string, mixed>|null  $payload
     */
    private function applyPaymentResult(
        Order $order,
        string $paymentId,
        string $status,
        float $amount,
        string $currency,
        ?array $payload,
    ): void {
        OrderPayment::query()->updateOrCreate(
            ['provider' => 'mercadopago', 'provider_payment_id' => $paymentId],
            [
                'order_id' => $order->id,
                'provider_preference_id' => $order->mercadopago_preference_id,
                'status' => $status,
                'amount' => $amount,
                'currency' => $currency,
                'raw_payload' => $payload,
            ],
        );

        match ($status) {
            'approved' => $this->orders->markAsPaid($order, $paymentId),
            'rejected', 'cancelled' => $this->orders->markAsFailed($order),
            default => $order->update(['payment_status' => 'pending']),
        };
    }

    private function resultUrl(Order $order, string $status): string
    {
        $base = rtrim((string) (config('app.frontend_url') ?: config('app.url')), '/');

        return $base.'/checkout/resultado?order='.$order->order_code.'&status='.$status;
    }

    /**
     * Determina si una URL es alcanzable públicamente por MercadoPago. Los
     * hosts locales (localhost, *.local, IPs privadas) hacen que MercadoPago
     * rechace notification_url/auto_return, por lo que en desarrollo se omiten.
     */
    private function isPublicUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            return false;
        }

        $host = strtolower($host);

        if ($host === 'localhost' || str_ends_with($host, '.local') || str_ends_with($host, '.test')) {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            // Rechaza IPs privadas o reservadas (127.0.0.1, 10.x, 192.168.x, etc.).
            return filter_var(
                $host,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            ) !== false;
        }

        // Un host sin punto (p. ej. "app") no es resoluble públicamente.
        return str_contains($host, '.');
    }

    private function verifySignature(Request $request, PaymentSetting $settings): bool
    {
        $secret = $settings->resolvedWebhookSecret();

        if ($secret === null) {
            return true;
        }

        $signature = $request->header('x-signature');
        $requestId = $request->header('x-request-id');
        $dataId = $this->resolvePaymentId($request);

        if ($signature === null || $dataId === null) {
            return false;
        }

        $parts = collect(explode(',', $signature))
            ->mapWithKeys(function (string $part): array {
                [$key, $value] = array_pad(explode('=', trim($part), 2), 2, '');

                return [trim($key) => trim($value)];
            });

        $ts = $parts->get('ts');
        $hash = $parts->get('v1');

        if ($ts === null || $hash === null) {
            return false;
        }

        $manifest = "id:{$dataId};request-id:{$requestId};ts:{$ts};";
        $expected = hash_hmac('sha256', $manifest, $secret);

        return hash_equals($expected, $hash);
    }

    private function requireAccessToken(PaymentSetting $settings): string
    {
        $token = $this->accessToken($settings);

        if ($token === null) {
            throw new \RuntimeException('MercadoPago no está configurado.');
        }

        return $token;
    }

    private function accessToken(PaymentSetting $settings): ?string
    {
        return $settings->resolvedAccessToken()
            ?? (is_string(config('services.mercadopago.access_token')) && config('services.mercadopago.access_token') !== ''
                ? config('services.mercadopago.access_token')
                : null);
    }
}
