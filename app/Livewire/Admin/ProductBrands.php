<?php

namespace App\Livewire\Admin;

use App\Models\ProductBrand;
use Illuminate\Support\Str;

class ProductBrands extends BaseResourceIndex
{
    protected string $modelClass = ProductBrand::class;

    protected string $permission = 'product-brands';

    protected string $title = 'Marcas';

    protected string $description = 'Marcas de la tienda, con logo para filtrar el catálogo.';

    /** @var array<string, string> */
    protected array $columns = ['id' => 'ID', 'name' => 'Nombre', 'slug' => 'Slug', 'status' => 'Estado'];

    /** @var array<string, array<string, mixed>> */
    protected array $fields = [
        'name' => ['label' => 'Nombre', 'rules' => ['required', 'string', 'max:255']],
        'slug' => ['label' => 'Slug', 'rules' => ['required', 'string', 'max:255'], 'unique' => true],
        'description' => ['label' => 'Descripción', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'image' => [
            'label' => 'Logo',
            'type' => 'image',
            'directory' => 'product-brands/{slug}',
            'rules' => ['nullable', 'string', 'max:255'],
        ],
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
