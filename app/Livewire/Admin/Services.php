<?php

namespace App\Livewire\Admin;

use App\Models\Service;
use Illuminate\Support\Str;

class Services extends BaseResourceIndex
{
    protected string $modelClass = Service::class;

    protected string $permission = 'services';

    protected string $title = 'Servicios';

    protected string $description = 'Catálogo de servicios del sitio.';

    /** @var list<string> */
    protected array $with = ['category'];

    /** @var array<string, string> */
    protected array $columns = ['id' => 'ID', 'title' => 'Título', 'slug' => 'Slug', 'status' => 'Estado'];

    /** @var array<string, array<string, mixed>> */
    protected array $fields = [
        'service_category_id' => [
            'label' => 'Categoría',
            'type' => 'select',
            'options' => ['model' => \App\Models\ServiceCategory::class, 'label' => 'name'],
            'rules' => ['nullable', 'integer', 'exists:service_categories,id'],
        ],
        'title' => ['label' => 'Título', 'rules' => ['required', 'string', 'max:255']],
        'slug' => ['label' => 'Slug', 'rules' => ['required', 'string', 'max:255'], 'unique' => true],
        'description' => ['label' => 'Descripción', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'icon' => ['label' => 'Icono Lucide', 'rules' => ['nullable', 'string', 'max:100']],
        'price_label' => ['label' => 'Precio', 'rules' => ['nullable', 'string', 'max:100']],
        'features' => ['label' => 'Features (JSON)', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'sort_order' => ['label' => 'Orden', 'type' => 'number', 'default' => 0, 'rules' => ['integer', 'min:0']],
        'status' => ['label' => 'Estado', 'type' => 'select', 'default' => 'published', 'options' => ['draft' => 'Borrador', 'published' => 'Publicado', 'archived' => 'Archivado'], 'rules' => ['required', 'string']],
    ];

    public function create(): void
    {
        parent::create();
        $this->form['slug'] = '';
    }

    public function updatedFormTitle(?string $value): void
    {
        if (($this->form['slug'] ?? '') === '' && $value) {
            $this->form['slug'] = Str::slug($value);
        }
    }

    protected function mergeUploadedMedia(array $data): array
    {
        if (isset($data['features']) && is_string($data['features'])) {
            $decoded = json_decode($data['features'], true);
            $data['features'] = is_array($decoded) ? $decoded : [];
        }

        return parent::mergeUploadedMedia($data);
    }
}
