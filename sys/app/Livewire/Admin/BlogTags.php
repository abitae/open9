<?php

namespace App\Livewire\Admin;

use App\Models\BlogTag;

class BlogTags extends BaseResourceIndex
{
    protected string $modelClass = BlogTag::class;

    protected string $permission = 'blog-tags';

    protected string $title = 'Etiquetas del blog';

    protected string $description = 'Etiquetas reutilizables.';

    /** @var array<string, string> */
    protected array $columns = ['id' => 'ID', 'name' => 'Nombre', 'slug' => 'Slug'];

    /** @var array<string, array<string, mixed>> */
    protected array $fields = [
        'name' => ['label' => 'Nombre', 'rules' => ['required', 'string', 'max:255']],
        'slug' => ['label' => 'Slug', 'rules' => ['required', 'string', 'max:255'], 'unique' => true],
    ];
}
