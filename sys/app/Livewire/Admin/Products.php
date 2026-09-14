<?php

namespace App\Livewire\Admin;

use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use Illuminate\Support\Str;

class Products extends BaseResourceIndex
{
    protected string $modelClass = Product::class;

    protected string $permission = 'products';

    protected string $title = 'Productos';

    protected string $description = 'Catálogo de la tienda.';

    /** @var list<string> */
    protected array $with = ['category', 'brand'];

    /** @var array<string, string> */
    protected array $columns = ['id' => 'ID', 'name' => 'Nombre', 'brand.name' => 'Marca', 'price' => 'Precio', 'stock' => 'Stock', 'status' => 'Estado'];

    /** @var array<string, array<string, mixed>> */
    protected array $fields = [
        'product_category_id' => [
            'label' => 'Categoría',
            'type' => 'select',
            'options' => ['model' => ProductCategory::class, 'label' => 'name'],
            'rules' => ['nullable', 'integer', 'exists:product_categories,id'],
        ],
        'product_brand_id' => [
            'label' => 'Marca',
            'type' => 'select',
            'options' => ['model' => ProductBrand::class, 'label' => 'name'],
            'rules' => ['nullable', 'integer', 'exists:product_brands,id'],
        ],
        'name' => ['label' => 'Nombre', 'rules' => ['required', 'string', 'max:255']],
        'slug' => ['label' => 'Slug', 'rules' => ['required', 'string', 'max:255'], 'unique' => true],
        'description' => ['label' => 'Descripción', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'price' => ['label' => 'Precio', 'type' => 'number', 'default' => 0, 'rules' => ['required', 'numeric', 'min:0']],
        'currency' => [
            'label' => 'Moneda base',
            'type' => 'select',
            'default' => 'USD',
            'options' => ['USD' => 'Dólares (USD)', 'PEN' => 'Soles (PEN)'],
            'rules' => ['required', 'string', 'in:USD,PEN'],
        ],
        'stock' => ['label' => 'Stock', 'type' => 'number', 'default' => 0, 'rules' => ['integer', 'min:0']],
        'badge' => ['label' => 'Badge', 'rules' => ['nullable', 'string', 'max:100']],
        'rating' => ['label' => 'Rating', 'type' => 'number', 'default' => 0, 'rules' => ['numeric', 'min:0', 'max:5']],
        'main_image' => ['label' => 'Imagen', 'type' => 'image', 'directory' => 'products/{slug}', 'rules' => ['nullable', 'string']],
        'sort_order' => ['label' => 'Orden', 'type' => 'number', 'default' => 0, 'rules' => ['integer', 'min:0']],
        'status' => ['label' => 'Estado', 'type' => 'select', 'default' => 'published', 'options' => ['draft' => 'Borrador', 'published' => 'Publicado', 'archived' => 'Archivado'], 'rules' => ['required', 'string']],
    ];

    public function updatedFormName(?string $value): void
    {
        if (($this->form['slug'] ?? '') === '' && $value) {
            $this->form['slug'] = Str::slug($value);
        }
    }
}
