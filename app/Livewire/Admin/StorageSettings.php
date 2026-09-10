<?php

namespace App\Livewire\Admin;

use App\Enums\StorageDriver;
use App\Models\StorageSetting;
use App\Services\SiteConfigService;
use App\Services\StorageConfigService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;
use Livewire\Component;

class StorageSettings extends Component
{
    public string $driver = 'local';

    public ?string $gcs_project_id = null;

    public ?string $gcs_bucket = null;

    public ?string $gcs_public_url = null;

    public ?string $local_public_url = null;

    public string $gcs_key_json = '';

    public ?string $driver_changed_at = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('storage-settings.view'), 403);

        $settings = StorageSetting::query()->firstOrCreate(['id' => 1], [
            'driver' => StorageDriver::Local,
        ]);

        $this->driver = $settings->driver->value;
        $this->gcs_project_id = $settings->gcs_project_id;
        $this->gcs_bucket = $settings->gcs_bucket;
        $this->gcs_public_url = $settings->gcs_public_url;
        $this->local_public_url = $settings->local_public_url;
        $this->driver_changed_at = $settings->driver_changed_at?->toDateTimeString();
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->can('storage-settings.update'), 403);

        $this->validate([
            'driver' => ['required', 'in:local,gcs'],
            'gcs_project_id' => ['required_if:driver,gcs', 'nullable', 'string', 'max:255'],
            'gcs_bucket' => ['required_if:driver,gcs', 'nullable', 'string', 'max:255'],
            'gcs_public_url' => ['required_if:driver,gcs', 'nullable', 'url', 'max:500'],
            'local_public_url' => ['nullable', 'url', 'max:500'],
            'gcs_key_json' => ['nullable', 'string'],
        ]);

        $settings = StorageSetting::query()->firstOrCreate(['id' => 1]);
        $driverChanged = $settings->driver->value !== $this->driver;

        $payload = [
            'driver' => $this->driver,
            'gcs_project_id' => $this->gcs_project_id,
            'gcs_bucket' => $this->gcs_bucket,
            'gcs_public_url' => $this->gcs_public_url,
            'local_public_url' => $this->local_public_url,
        ];

        if ($this->gcs_key_json !== '') {
            $payload['gcs_key_json'] = Crypt::encryptString($this->gcs_key_json);
        }

        if ($driverChanged) {
            $payload['driver_changed_at'] = now();
        }

        $settings->update($payload);
        app(StorageConfigService::class)->clearCache();
        app(SiteConfigService::class)->clearCache();

        $this->gcs_key_json = '';
        $this->driver_changed_at = $settings->fresh()->driver_changed_at?->toDateTimeString();

        session()->flash('status', 'Configuración de almacenamiento guardada.');
    }

    public function testConnection(): void
    {
        abort_unless(auth()->user()?->can('storage-settings.update'), 403);

        try {
            $ok = app(StorageConfigService::class)->testConnection();
            session()->flash('status', $ok ? 'Conexión exitosa.' : 'No se pudo verificar la conexión.');
        } catch (\Throwable $exception) {
            session()->flash('status', 'Error: '.$exception->getMessage());
        }
    }

    public function render(): View
    {
        return view('livewire.admin.storage-settings');
    }
}
