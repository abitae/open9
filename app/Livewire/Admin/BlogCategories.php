<?php

namespace App\Livewire\Admin;

use App\Models\BlogCategory;

class BlogCategories extends BaseResourceIndex
{
    protected string $modelClass = BlogCategory::class;

    protected string $permission = 'blog-categories';

    protected string $title = 'Categorías del blog';

    protected string $description = 'Taxonomía del blog.';

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
            'directory' => 'blog-categories/{slug}',
            'rules' => ['nullable', 'string', 'max:255'],
        ],
        'status' => ['label' => 'Estado', 'type' => 'select', 'default' => 'active', 'options' => ['active' => 'Activo', 'inactive' => 'Inactivo'], 'rules' => ['required', 'string']],
        'sort_order' => ['label' => 'Orden', 'type' => 'number', 'default' => 0, 'rules' => ['integer', 'min:0']],
    ];
}
