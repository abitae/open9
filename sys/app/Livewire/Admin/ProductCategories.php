<?php

namespace App\Livewire\Admin;

use App\Models\ProductCategory;
use Illuminate\Support\Str;

class ProductCategories extends BaseResourceIndex
{
    protected string $modelClass = ProductCategory::class;

    protected string $permission = 'product-categories';

    protected string $title = 'Categorías de productos';

    protected string $description = 'Taxonomía de la tienda.';

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
