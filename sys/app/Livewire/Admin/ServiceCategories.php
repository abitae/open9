<?php

namespace App\Livewire\Admin;

use App\Models\ServiceCategory;
use Illuminate\Support\Str;

class ServiceCategories extends BaseResourceIndex
{
    protected string $modelClass = ServiceCategory::class;

    protected string $permission = 'service-categories';

    protected string $title = 'Categorías de servicios';

    protected string $description = 'Taxonomía de servicios.';

    /** @var array<string, string> */
    protected array $columns = ['id' => 'ID', 'name' => 'Nombre', 'slug' => 'Slug', 'status' => 'Estado'];

    /** @var array<string, array<string, mixed>> */
    protected array $fields = [
        'name' => ['label' => 'Nombre', 'rules' => ['required', 'string', 'max:255']],
        'slug' => ['label' => 'Slug', 'rules' => ['required', 'string', 'max:255'], 'unique' => true],
        'description' => ['label' => 'Descripción', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'sort_order' => ['label' => 'Orden', 'type' => 'number', 'default' => 0, 'rules' => ['integer', 'min:0']],
        'status' => ['label' => 'Estado', 'type' => 'select', 'default' => 'active', 'options' => ['active' => 'Activo', 'inactive' => 'Inactivo'], 'rules' => ['required', 'string']],
    ];

    public function updatedFormName(?string $value): void
    {
        if (($this->form['slug'] ?? '') === '' && $value) {
            $this->form['slug'] = Str::slug($value);
        }
    }
}
