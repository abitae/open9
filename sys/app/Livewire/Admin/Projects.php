<?php

namespace App\Livewire\Admin;

use App\Models\Project;
use App\Models\ProjectCategory;

class Projects extends BaseResourceIndex
{
    protected string $modelClass = Project::class;

    protected string $permission = 'projects';

    protected string $title = 'Proyectos';

    protected string $description = 'Entradas del portafolio.';

    /** @var list<string> */
    protected array $with = ['category'];

    /** @var array<string, string> */
    protected array $columns = ['id' => 'ID', 'title' => 'Título', 'status' => 'Estado', 'is_featured' => 'Destacado'];

    /** @var array<string, array<string, mixed>> */
    protected array $fields = [
        'project_category_id' => [
            'label' => 'Categoría',
            'type' => 'select',
            'options' => ['model' => ProjectCategory::class, 'label' => 'name'],
            'detail_relation' => 'category.name',
            'rules' => ['required', 'integer'],
        ],
        'title' => ['label' => 'Título', 'rules' => ['required', 'string', 'max:255']],
        'slug' => ['label' => 'Slug', 'rules' => ['required', 'string', 'max:255'], 'unique' => true],
        'short_description' => ['label' => 'Descripción corta', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'main_image' => [
            'label' => 'Imagen principal',
            'type' => 'image',
            'directory' => 'projects/{slug}',
            'rules' => ['nullable', 'string', 'max:255'],
        ],
        'gallery' => [
            'label' => 'Galería (imágenes y videos)',
            'type' => 'gallery',
            'directory' => 'projects/{slug}/gallery',
            'default' => [],
            'rules' => ['nullable', 'array'],
        ],
        'status' => ['label' => 'Estado', 'type' => 'select', 'default' => 'draft', 'options' => ['draft' => 'Borrador', 'published' => 'Publicado', 'archived' => 'Archivado'], 'rules' => ['required', 'string']],
        'is_featured' => ['label' => 'Destacado', 'type' => 'checkbox', 'default' => false, 'rules' => ['boolean']],
    ];

    /** @var array<string, array<string, mixed>> */
    protected array $detailOnlyFields = [
        'description' => ['label' => 'Descripción completa', 'type' => 'textarea'],
        'client_name' => ['label' => 'Cliente'],
        'project_url' => ['label' => 'URL del proyecto', 'type' => 'url'],
        'github_url' => ['label' => 'Repositorio GitHub', 'type' => 'url'],
        'views_count' => ['label' => 'Vistas'],
        'published_at' => ['label' => 'Publicado', 'type' => 'datetime'],
    ];
}
