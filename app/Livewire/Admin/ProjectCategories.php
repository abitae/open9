<?php

namespace App\Livewire\Admin;

use App\Models\ProjectCategory;

class ProjectCategories extends BaseResourceIndex
{
    protected string $modelClass = ProjectCategory::class;

    protected string $permission = 'project-categories';

    protected string $title = 'Categorías de proyectos';

    protected string $description = 'Taxonomía del portafolio.';

    /** @var array<string, string> */
    protected array $columns = ['id' => 'ID', 'name' => 'Nombre', 'slug' => 'Slug', 'status' => 'Estado'];

    /** @var array<string, array<string, mixed>> */
    protected array $fields = [
        'name' => ['label' => 'Nombre', 'rules' => ['required', 'string', 'max:255']],
        'slug' => ['label' => 'Slug', 'rules' => ['required', 'string', 'max:255'], 'unique' => true],
        'description' => ['label' => 'Descripción', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'image' => [
            'label' => 'Imagen',
            'type' => 'image',
            'directory' => 'project-categories/{slug}',
            'rules' => ['nullable', 'string', 'max:255'],
        ],
        'status' => ['label' => 'Estado', 'type' => 'select', 'default' => 'active', 'options' => ['active' => 'Activo', 'inactive' => 'Inactivo'], 'rules' => ['required', 'string']],
        'sort_order' => ['label' => 'Orden', 'type' => 'number', 'default' => 0, 'rules' => ['integer', 'min:0']],
    ];
}
