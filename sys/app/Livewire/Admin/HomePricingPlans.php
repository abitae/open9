<?php

namespace App\Livewire\Admin;

use App\Models\HomePricingPlan;

class HomePricingPlans extends BaseResourceIndex
{
    protected string $modelClass = HomePricingPlan::class;

    protected string $permission = 'home-pricing-plans';

    protected string $title = 'Planes de precios';

    protected string $description = 'Pricing del home.';

    /** @var array<string, string> */
    protected array $columns = ['id' => 'ID', 'name' => 'Nombre', 'price' => 'Precio', 'sort_order' => 'Orden'];

    /** @var array<string, array<string, mixed>> */
    protected array $fields = [
        'name' => ['label' => 'Nombre', 'rules' => ['required', 'string', 'max:255']],
        'price' => ['label' => 'Precio', 'rules' => ['required', 'string', 'max:100']],
        'period' => ['label' => 'Periodo', 'rules' => ['nullable', 'string', 'max:100']],
        'description' => ['label' => 'Descripción', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'features' => ['label' => 'Features (JSON array)', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'cta_text' => ['label' => 'CTA texto', 'rules' => ['nullable', 'string', 'max:255']],
        'cta_url' => ['label' => 'CTA URL', 'rules' => ['nullable', 'string', 'max:500']],
        'is_highlighted' => ['label' => 'Destacado', 'type' => 'checkbox', 'default' => false, 'rules' => ['boolean']],
        'sort_order' => ['label' => 'Orden', 'type' => 'number', 'default' => 0, 'rules' => ['integer', 'min:0']],
        'is_visible' => ['label' => 'Visible', 'type' => 'checkbox', 'default' => true, 'rules' => ['boolean']],
        'status' => ['label' => 'Estado', 'type' => 'select', 'default' => 'active', 'options' => ['active' => 'Activo', 'inactive' => 'Inactivo'], 'rules' => ['required', 'string']],
    ];

    protected function mergeUploadedMedia(array $data): array
    {
        if (isset($data['features']) && is_string($data['features'])) {
            $decoded = json_decode($data['features'], true);
            $data['features'] = is_array($decoded) ? $decoded : [];
        }

        return parent::mergeUploadedMedia($data);
    }
}
