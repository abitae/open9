<?php

namespace App\Livewire\Admin;

use App\Models\Course;
use App\Models\CourseModule;

class CourseModules extends BaseResourceIndex
{
    protected string $modelClass = CourseModule::class;

    protected string $permission = 'course-modules';

    protected string $title = 'Módulos del curso';

    protected string $description = 'Secciones ordenadas del curso.';

    /** @var array<string, string> */
    protected array $columns = ['id' => 'ID', 'title' => 'Título', 'sort_order' => 'Orden', 'status' => 'Estado'];

    /** @var array<string, array<string, mixed>> */
    protected array $fields = [
        'course_id' => ['label' => 'Curso', 'type' => 'select', 'options' => ['model' => Course::class, 'label' => 'title'], 'rules' => ['required', 'integer']],
        'title' => ['label' => 'Título', 'rules' => ['required', 'string', 'max:255']],
        'description' => ['label' => 'Descripción', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'sort_order' => ['label' => 'Orden', 'type' => 'number', 'default' => 0, 'rules' => ['integer', 'min:0']],
        'status' => ['label' => 'Estado', 'type' => 'select', 'default' => 'active', 'options' => ['active' => 'Activo', 'inactive' => 'Inactivo'], 'rules' => ['required', 'string']],
    ];
}
