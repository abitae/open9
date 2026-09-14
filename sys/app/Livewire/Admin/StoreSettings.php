<?php

namespace App\Livewire\Admin;

use App\Enums\SettingType;
use App\Models\Setting;
use App\Services\SiteConfigService;
use Illuminate\View\View;
use Livewire\Component;

class StoreSettings extends Component
{
    public bool $allow_negative_stock = false;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('store-settings.view'), 403);

        $this->allow_negative_stock = app(SiteConfigService::class)->allowsNegativeStock();
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->can('store-settings.update'), 403);

        $this->validate([
            'allow_negative_stock' => ['boolean'],
        ]);

        Setting::query()->updateOrCreate(
            ['group' => 'store', 'key' => 'allow_negative_stock'],
            [
                'value' => $this->allow_negative_stock ? '1' : '0',
                'type' => SettingType::Boolean,
                'is_public' => true,
            ],
        );

        session()->flash('status', 'Configuración de inventario guardada.');
    }

    public function render(): View
    {
        return view('livewire.admin.store-settings');
    }
}
