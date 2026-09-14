<?php

namespace App\Livewire\Admin;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;

class BlogPosts extends BaseResourceIndex
{
    protected string $modelClass = BlogPost::class;

    protected string $permission = 'blog';

    protected string $title = 'Blog';

    protected string $description = 'Publicaciones y estado editorial.';

    /** @var list<string> */
    protected array $with = ['author', 'category', 'tags'];

    /** @var array<string, string> */
    protected array $columns = ['id' => 'ID', 'title' => 'Título', 'status' => 'Estado', 'views_count' => 'Vistas'];

    /** @var array<string, array<string, mixed>> */
    protected array $fields = [
        'user_id' => [
            'label' => 'Autor',
            'type' => 'select',
            'options' => ['model' => User::class, 'label' => 'name'],
            'detail_relation' => 'author.name',
            'rules' => ['required', 'integer'],
        ],
        'blog_category_id' => [
            'label' => 'Categoría',
            'type' => 'select',
            'options' => ['model' => BlogCategory::class, 'label' => 'name'],
            'detail_relation' => 'category.name',
            'rules' => ['required', 'integer'],
        ],
        'title' => ['label' => 'Título', 'rules' => ['required', 'string', 'max:255']],
        'slug' => ['label' => 'Slug', 'rules' => ['required', 'string', 'max:255'], 'unique' => true],
        'excerpt' => ['label' => 'Extracto', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'content' => ['label' => 'Contenido', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'main_image' => [
            'label' => 'Imagen principal',
            'type' => 'image',
            'directory' => 'blog/{slug}',
            'rules' => ['nullable', 'string', 'max:255'],
        ],
        'video_url' => [
            'label' => 'Video principal',
            'type' => 'video',
            'directory' => 'blog/{slug}/videos',
            'rules' => ['nullable', 'string', 'max:255'],
        ],
        'gallery' => [
            'label' => 'Galería (imágenes y videos)',
            'type' => 'gallery',
            'directory' => 'blog/{slug}/gallery',
            'default' => [],
            'rules' => ['nullable', 'array'],
        ],
        'status' => ['label' => 'Estado', 'type' => 'select', 'default' => 'draft', 'options' => ['draft' => 'Borrador', 'published' => 'Publicado', 'scheduled' => 'Programado', 'archived' => 'Archivado'], 'rules' => ['required', 'string']],
    ];

    /** @var array<string, array<string, mixed>> */
    protected array $detailOnlyFields = [
        'is_featured' => ['label' => 'Destacado', 'type' => 'checkbox'],
        'reading_time' => ['label' => 'Tiempo de lectura (min)'],
        'views_count' => ['label' => 'Vistas'],
        'published_at' => ['label' => 'Publicado', 'type' => 'datetime'],
        'tags' => ['label' => 'Etiquetas', 'type' => 'tags'],
    ];
}
