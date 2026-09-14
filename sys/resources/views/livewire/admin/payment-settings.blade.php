<section class="space-y-4">
    <div>
        <flux:heading size="xl">Pasarela de pagos</flux:heading>
        <flux:text class="text-xs">Configura MercadoPago Checkout Bricks (cobro en el sitio, sin redirección) para la tienda. Las credenciales se guardan cifradas.</flux:text>
    </div>

    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    <form wire:submit="save" class="space-y-4 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
        <flux:checkbox wire:model="form.is_enabled" label="Pasarela habilitada (cobros activos en la tienda)" />

        <div class="grid gap-3 md:grid-cols-3">
            <flux:select wire:model="form.provider" label="Proveedor">
                <option value="mercadopago">MercadoPago</option>
            </flux:select>
            <flux:select wire:model="form.mode" label="Modo">
                <option value="sandbox">Sandbox (pruebas)</option>
                <option value="production">Producción</option>
            </flux:select>
            <flux:select wire:model="form.currency" label="Moneda de cobro">
                <option value="PEN">Soles (PEN)</option>
                <option value="USD">Dólares (USD)</option>
                <option value="ARS">Pesos AR (ARS)</option>
                <option value="MXN">Pesos MX (MXN)</option>
                <option value="CLP">Pesos CL (CLP)</option>
                <option value="COP">Pesos CO (COP)</option>
            </flux:select>
        </div>

        <flux:input wire:model="form.statement_descriptor" label="Descriptor en el estado de cuenta" placeholder="OPEN9" />

        <flux:separator text="Credenciales de producción" />
        <div class="grid gap-3 md:grid-cols-2">
            <flux:input wire:model="form.public_key" label="Public Key (producción)" placeholder="APP_USR-..." />
            <flux:input wire:model="access_token" label="Access Token (producción, vacío = no cambiar)" type="password" placeholder="APP_USR-..." />
        </div>

        <flux:separator text="Credenciales de sandbox" />
        <div class="grid gap-3 md:grid-cols-2">
            <flux:input wire:model="form.sandbox_public_key" label="Public Key (sandbox)" placeholder="TEST-..." />
            <flux:input wire:model="sandbox_access_token" label="Access Token (sandbox, vacío = no cambiar)" type="password" placeholder="TEST-..." />
        </div>

        <flux:separator text="Webhook" />
        <flux:input wire:model="webhook_secret" label="Secreto de webhook / firma (vacío = no cambiar)" type="password" />
        <flux:text class="text-xs text-zinc-500">
            URL de notificaciones para MercadoPago: <code>{{ url('/api/webhooks/mercadopago') }}</code>
        </flux:text>

        <flux:button type="submit" variant="primary">Guardar</flux:button>
    </form>

    <div class="space-y-3 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
        <flux:heading size="sm">Probar conexión ({{ ($form['mode'] ?? 'sandbox') === 'production' ? 'Producción' : 'Sandbox' }})</flux:heading>
        <flux:text class="text-xs text-zinc-500">Verifica el access token del modo activo contra la API de MercadoPago.</flux:text>
        <flux:button type="button" wire:click="testConnection">Enviar prueba</flux:button>
        @if ($test_result)
            <flux:text class="text-sm whitespace-pre-wrap">{{ $test_result }}</flux:text>
        @endif
    </div>
</section>
