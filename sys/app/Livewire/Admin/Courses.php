<?php

namespace App\Livewire\Admin;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Instructor;

class Courses extends BaseResourceIndex
{
    protected string $modelClass = Course::class;

    protected string $permission = 'courses';

    protected string $title = 'Cursos';

    protected string $description = 'Catálogo de cursos.';

    protected ?string $builderRouteName = 'admin.courses.builder';

    protected string $builderLabel = 'Currícula';

    /** @var array<string, string> */
    protected array $columns = ['id' => 'ID', 'title' => 'Título', 'modality' => 'Modalidad', 'status' => 'Estado'];

    /** @var array<string, array<string, mixed>> */
    protected array $fields = [
        'course_category_id' => ['label' => 'Categoría', 'type' => 'select', 'options' => ['model' => CourseCategory::class, 'label' => 'name'], 'rules' => ['required', 'integer']],
        'instructor_id' => ['label' => 'Instructor', 'type' => 'select', 'options' => ['model' => Instructor::class, 'label' => 'name'], 'rules' => ['required', 'integer']],
        'title' => ['label' => 'Título', 'rules' => ['required', 'string', 'max:255']],
        'slug' => ['label' => 'Slug', 'rules' => ['required', 'string', 'max:255'], 'unique' => true],
        'subtitle' => ['label' => 'Subtítulo', 'rules' => ['nullable', 'string', 'max:255']],
        'description' => ['label' => 'Descripción', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'modality' => ['label' => 'Modalidad', 'type' => 'select', 'default' => 'virtual', 'options' => ['presencial' => 'Presencial', 'virtual' => 'Virtual', 'hibrido' => 'Híbrido', 'grabado' => 'Grabado'], 'rules' => ['required', 'string']],
        'level' => ['label' => 'Nivel', 'type' => 'select', 'default' => 'basico', 'options' => ['basico' => 'Básico', 'intermedio' => 'Intermedio', 'avanzado' => 'Avanzado'], 'rules' => ['required', 'string']],
        'price' => ['label' => 'Precio', 'type' => 'number', 'default' => 0, 'rules' => ['numeric', 'min:0']],
        'image' => ['label' => 'Imagen de portada', 'rules' => ['nullable', 'string', 'max:255']],
        'promotional_video_url' => ['label' => 'Video de presentación', 'rules' => ['nullable', 'url', 'max:255']],
        'syllabus_file' => ['label' => 'Archivo de temario', 'rules' => ['nullable', 'string', 'max:255']],
        'status' => ['label' => 'Estado', 'type' => 'select', 'default' => 'draft', 'options' => ['draft' => 'Borrador', 'published' => 'Publicado', 'closed' => 'Cerrado', 'archived' => 'Archivado'], 'rules' => ['required', 'string']],
    ];
}
