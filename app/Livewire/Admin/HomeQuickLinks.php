<?php

namespace App\Livewire\Admin;

use App\Models\HomeQuickLink;

class HomeQuickLinks extends BaseResourceIndex
{
    protected string $modelClass = HomeQuickLink::class;

    protected string $permission = 'home-quick-links';

    protected string $title = 'Enlaces rápidos';

    protected string $description = 'Quick links del home.';

    /** @var array<string, string> */
    protected array $columns = ['id' => 'ID', 'title' => 'Título', 'link_url' => 'URL', 'sort_order' => 'Orden'];

    /** @var array<string, array<string, mixed>> */
    protected array $fields = [
        'title' => ['label' => 'Título', 'rules' => ['required', 'string', 'max:255']],
        'description' => ['label' => 'Descripción', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'link_url' => ['label' => 'URL', 'rules' => ['required', 'string', 'max:500']],
        'icon' => ['label' => 'Icono Lucide', 'rules' => ['nullable', 'string', 'max:100']],
        'sort_order' => ['label' => 'Orden', 'type' => 'number', 'default' => 0, 'rules' => ['integer', 'min:0']],
        'is_visible' => ['label' => 'Visible', 'type' => 'checkbox', 'default' => true, 'rules' => ['boolean']],
        'status' => ['label' => 'Estado', 'type' => 'select', 'default' => 'active', 'options' => ['active' => 'Activo', 'inactive' => 'Inactivo'], 'rules' => ['required', 'string']],
    ];
}
