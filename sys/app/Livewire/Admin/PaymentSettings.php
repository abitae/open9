<?php

namespace App\Livewire\Admin;

use App\Models\PaymentSetting;
use App\Services\SiteConfigService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Livewire\Component;

class PaymentSettings extends Component
{
    /** @var array<string, mixed> */
    public array $form = [];

    public string $access_token = '';

    public string $sandbox_access_token = '';

    public string $webhook_secret = '';

    public ?string $test_result = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('payment-settings.view'), 403);

        $settings = PaymentSetting::current();

        $this->form = $settings->only([
            'provider', 'is_enabled', 'mode', 'currency',
            'public_key', 'sandbox_public_key', 'statement_descriptor',
        ]);
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->can('payment-settings.update'), 403);

        $this->validate([
            'form.provider' => ['required', 'string', 'in:mercadopago'],
            'form.is_enabled' => ['boolean'],
            'form.mode' => ['required', 'string', 'in:sandbox,production'],
            'form.currency' => ['required', 'string', 'in:PEN,USD,ARS,MXN,CLP,COP'],
            'form.public_key' => ['nullable', 'string', 'max:255'],
            'form.sandbox_public_key' => ['nullable', 'string', 'max:255'],
            'form.statement_descriptor' => ['nullable', 'string', 'max:255'],
            'access_token' => ['nullable', 'string'],
            'sandbox_access_token' => ['nullable', 'string'],
            'webhook_secret' => ['nullable', 'string'],
        ]);

        $payload = $this->form;

        if ($this->access_token !== '') {
            $payload['access_token'] = Crypt::encryptString($this->access_token);
        }

        if ($this->sandbox_access_token !== '') {
            $payload['sandbox_access_token'] = Crypt::encryptString($this->sandbox_access_token);
        }

        if ($this->webhook_secret !== '') {
            $payload['webhook_secret'] = Crypt::encryptString($this->webhook_secret);
        }

        PaymentSetting::query()->updateOrCreate(['id' => 1], $payload);
        app(SiteConfigService::class)->clearCache();

        $this->access_token = '';
        $this->sandbox_access_token = '';
        $this->webhook_secret = '';

        session()->flash('status', 'Configuración de pagos guardada.');
    }

    public function testConnection(): void
    {
        abort_unless(auth()->user()?->can('payment-settings.update'), 403);

        $settings = PaymentSetting::current();
        $token = $settings->resolvedAccessToken();

        if ($token === null) {
            $this->test_result = 'No hay access token configurado para el modo '.($settings->mode ?? 'sandbox').'.';

            return;
        }

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->get('https://api.mercadopago.com/users/me');

            if ($response->successful()) {
                $nickname = $response->json('nickname') ?? $response->json('email') ?? 'cuenta';
                $siteId = $response->json('site_id') ?? '';
                $this->test_result = "Conexión exitosa con MercadoPago ({$nickname}, {$siteId}).";
            } else {
                $this->test_result = 'Error '.$response->status().': '.$response->json('message', 'credenciales inválidas.');
            }
        } catch (\Throwable $exception) {
            $this->test_result = 'Error: '.$exception->getMessage();
        }
    }

    public function render(): View
    {
        return view('livewire.admin.payment-settings');
    }
}
