<?php

namespace App\Livewire\Admin;

use App\Models\HomeWorkflowStep;

class HomeWorkflowSteps extends BaseResourceIndex
{
    protected string $modelClass = HomeWorkflowStep::class;

    protected string $permission = 'home-workflow-steps';

    protected string $title = 'Pasos de metodología';

    protected string $description = 'Workflow del home.';

    /** @var array<string, string> */
    protected array $columns = ['id' => 'ID', 'step_number' => 'Paso', 'title' => 'Título', 'sort_order' => 'Orden'];

    /** @var array<string, array<string, mixed>> */
    protected array $fields = [
        'step_number' => ['label' => 'Número de paso', 'type' => 'number', 'default' => 1, 'rules' => ['integer', 'min:1']],
        'title' => ['label' => 'Título', 'rules' => ['required', 'string', 'max:255']],
        'description' => ['label' => 'Descripción', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'icon' => ['label' => 'Icono Lucide', 'rules' => ['nullable', 'string', 'max:100']],
        'sort_order' => ['label' => 'Orden', 'type' => 'number', 'default' => 0, 'rules' => ['integer', 'min:0']],
        'is_visible' => ['label' => 'Visible', 'type' => 'checkbox', 'default' => true, 'rules' => ['boolean']],
        'status' => ['label' => 'Estado', 'type' => 'select', 'default' => 'active', 'options' => ['active' => 'Activo', 'inactive' => 'Inactivo'], 'rules' => ['required', 'string']],
    ];
}
