<?php

namespace App\Livewire\Admin;

use App\Models\HomeStat;

class HomeStats extends BaseResourceIndex
{
    protected string $modelClass = HomeStat::class;

    protected string $permission = 'home-stats';

    protected string $title = 'Métricas del home';

    protected string $description = 'Estadísticas destacadas en la página principal.';

    /** @var array<string, string> */
    protected array $columns = ['id' => 'ID', 'value' => 'Valor', 'title' => 'Título', 'sort_order' => 'Orden', 'status' => 'Estado'];

    /** @var array<string, array<string, mixed>> */
    protected array $fields = [
        'value' => ['label' => 'Valor', 'rules' => ['required', 'string', 'max:50']],
        'suffix' => ['label' => 'Sufijo', 'rules' => ['nullable', 'string', 'max:50']],
        'title' => ['label' => 'Título', 'rules' => ['required', 'string', 'max:255']],
        'icon' => ['label' => 'Icono Lucide', 'rules' => ['nullable', 'string', 'max:100']],
        'sort_order' => ['label' => 'Orden', 'type' => 'number', 'default' => 0, 'rules' => ['integer', 'min:0']],
        'is_visible' => ['label' => 'Visible', 'type' => 'checkbox', 'default' => true, 'rules' => ['boolean']],
        'status' => ['label' => 'Estado', 'type' => 'select', 'default' => 'active', 'options' => ['active' => 'Activo', 'inactive' => 'Inactivo'], 'rules' => ['required', 'string']],
    ];
}
