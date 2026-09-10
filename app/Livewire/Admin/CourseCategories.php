<?php

namespace App\Livewire\Admin;

use App\Models\CourseCategory;

class CourseCategories extends BaseResourceIndex
{
    protected string $modelClass = CourseCategory::class;

    protected string $permission = 'course-categories';

    protected string $title = 'Categorías de cursos';

    protected string $description = 'Taxonomía de cursos.';

    /** @var array<string, string> */
    protected array $columns = ['id' => 'ID', 'name' => 'Nombre', 'slug' => 'Slug', 'status' => 'Estado'];

    /** @var array<string, array<string, mixed>> */
    protected array $fields = [
        'name' => ['label' => 'Nombre', 'rules' => ['required', 'string', 'max:255']],
        'slug' => ['label' => 'Slug', 'rules' => ['required', 'string', 'max:255'], 'unique' => true],
        'icon' => ['label' => 'Icono', 'rules' => ['nullable', 'string', 'max:255']],
        'status' => ['label' => 'Estado', 'type' => 'select', 'default' => 'active', 'options' => ['active' => 'Activo', 'inactive' => 'Inactivo'], 'rules' => ['required', 'string']],
        'sort_order' => ['label' => 'Orden', 'type' => 'number', 'default' => 0, 'rules' => ['integer', 'min:0']],
    ];
}
