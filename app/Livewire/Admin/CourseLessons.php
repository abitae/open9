<?php

namespace App\Livewire\Admin;

use App\Models\Course;
use App\Models\CourseLesson;

class CourseLessons extends BaseResourceIndex
{
    protected string $modelClass = CourseLesson::class;

    protected string $permission = 'course-lessons';

    protected string $title = 'Lecciones del curso';

    protected string $description = 'Contenido de las lecciones.';

    /** @var array<string, string> */
    protected array $columns = ['id' => 'ID', 'title' => 'Título', 'duration_minutes' => 'Min', 'status' => 'Estado'];

    /** @var array<string, array<string, mixed>> */
    protected array $fields = [
        'course_id' => ['label' => 'Curso', 'type' => 'select', 'options' => ['model' => Course::class, 'label' => 'title'], 'rules' => ['required', 'integer']],
        'title' => ['label' => 'Título', 'rules' => ['required', 'string', 'max:255']],
        'description' => ['label' => 'Descripción', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'video_url' => ['label' => 'Video de la lección', 'rules' => ['nullable', 'url', 'max:255']],
        'content' => ['label' => 'Contenido', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'resources' => ['label' => 'Recursos / archivos', 'type' => 'textarea', 'rules' => ['nullable']],
        'duration_minutes' => ['label' => 'Minutos', 'type' => 'number', 'default' => 0, 'rules' => ['integer', 'min:0']],
        'sort_order' => ['label' => 'Orden', 'type' => 'number', 'default' => 0, 'rules' => ['integer', 'min:0']],
        'is_preview' => ['label' => 'Vista previa', 'type' => 'checkbox', 'default' => false, 'rules' => ['boolean']],
        'status' => ['label' => 'Estado', 'type' => 'select', 'default' => 'active', 'options' => ['active' => 'Activo', 'inactive' => 'Inactivo'], 'rules' => ['required', 'string']],
    ];
}
