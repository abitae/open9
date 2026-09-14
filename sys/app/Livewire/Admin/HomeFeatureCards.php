<?php

namespace App\Livewire\Admin;

use App\Models\HomeFeatureCard;

class HomeFeatureCards extends BaseResourceIndex
{
    protected string $modelClass = HomeFeatureCard::class;

    protected string $permission = 'home-feature-cards';

    protected string $title = 'Cards de servicios';

    protected string $description = 'Tarjetas de servicios y soluciones del home.';

    /** @var array<string, string> */
    protected array $columns = ['id' => 'ID', 'title' => 'Título', 'card_type' => 'Tipo', 'sort_order' => 'Orden', 'status' => 'Estado'];

    /** @var array<string, array<string, mixed>> */
    protected array $fields = [
        'card_type' => ['label' => 'Tipo', 'type' => 'select', 'default' => 'service', 'options' => ['service' => 'Servicio', 'solution' => 'Solución'], 'rules' => ['required', 'string']],
        'client_type' => ['label' => 'Tipo de cliente', 'rules' => ['nullable', 'string', 'max:255']],
        'title' => ['label' => 'Título', 'rules' => ['required', 'string', 'max:255']],
        'description' => ['label' => 'Descripción', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'icon' => ['label' => 'Icono Lucide', 'rules' => ['nullable', 'string', 'max:100']],
        'sort_order' => ['label' => 'Orden', 'type' => 'number', 'default' => 0, 'rules' => ['integer', 'min:0']],
        'is_visible' => ['label' => 'Visible', 'type' => 'checkbox', 'default' => true, 'rules' => ['boolean']],
        'status' => ['label' => 'Estado', 'type' => 'select', 'default' => 'active', 'options' => ['active' => 'Activo', 'inactive' => 'Inactivo'], 'rules' => ['required', 'string']],
    ];
}
