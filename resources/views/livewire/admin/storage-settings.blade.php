<section class="space-y-4">
    <div>
        <flux:heading size="xl">Almacenamiento</flux:heading>
        <flux:text class="text-xs">Configura el driver de archivos para logos, imágenes y medios.</flux:text>
    </div>

    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    <form wire:submit="save" class="space-y-4 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
        <flux:select wire:model.live="driver" label="Driver">
            <flux:select.option value="local">Laravel local (public)</flux:select.option>
            <flux:select.option value="gcs">Google Cloud Storage</flux:select.option>
        </flux:select>

        @if ($driver === 'local')
            <flux:input wire:model="local_public_url" label="URL pública local (opcional)" placeholder="https://tudominio.com/storage" />
        @endif

        @if ($driver === 'gcs')
            <div class="grid gap-3 md:grid-cols-2">
                <flux:input wire:model="gcs_project_id" label="Project ID" />
                <flux:input wire:model="gcs_bucket" label="Bucket" />
            </div>
            <flux:input wire:model="gcs_public_url" label="URL pública del bucket" placeholder="https://storage.googleapis.com/mi-bucket" />
            <label class="space-y-1 text-sm">
                <span class="font-medium">Credenciales JSON (cuenta de servicio)</span>
                <textarea wire:model="gcs_key_json" rows="6" class="w-full rounded-md border border-zinc-300 bg-white p-2 text-xs dark:border-zinc-600 dark:bg-zinc-900" placeholder="Pega el JSON de la cuenta de servicio para actualizar"></textarea>
            </label>
        @endif

        @if ($driver_changed_at)
            <flux:text class="text-xs">Último cambio de driver: {{ $driver_changed_at }}</flux:text>
        @endif

        <div class="flex gap-2">
            <flux:button type="submit" variant="primary">Guardar</flux:button>
            <flux:button type="button" wire:click="testConnection">Probar conexión</flux:button>
        </div>
    </form>
</section>
