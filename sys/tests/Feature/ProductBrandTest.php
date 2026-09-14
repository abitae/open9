<?php

use App\Livewire\Admin\ProductBrands;
use App\Livewire\Admin\Products;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\User;
use App\Services\SiteConfigService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(DatabaseSeeder::class);
    app(SiteConfigService::class)->clearCache();
});

it('lets an admin create a product brand with a logo', function (): void {
    Storage::fake('public');

    $admin = User::query()->where('email', 'admin@open9.dev')->firstOrFail();
    $suffix = uniqid();

    Livewire::actingAs($admin)
        ->test(ProductBrands::class)
        ->call('create')
        ->set('form.name', 'Lenovo')
        ->set('form.slug', 'lenovo-'.$suffix)
        ->set('form.description', 'Laptops y workstations.')
        ->set('form.status', 'active')
        ->set('form.sort_order', 9)
        ->set('uploads.image', UploadedFile::fake()->image('lenovo.png'))
        ->call('save')
        ->assertHasNoErrors();

    $brand = ProductBrand::query()->where('slug', 'lenovo-'.$suffix)->firstOrFail();

    expect($brand->name)->toBe('Lenovo')
        ->and($brand->image)->not->toBeEmpty();

    Storage::disk('public')->assertExists($brand->image);
});

it('lets an admin assign a brand to a product', function (): void {
    $admin = User::query()->where('email', 'admin@open9.dev')->firstOrFail();
    $brand = ProductBrand::factory()->create(['name' => 'Supermicro', 'slug' => 'supermicro-'.uniqid()]);
    $product = Product::query()->where('status', 'published')->firstOrFail();

    Livewire::actingAs($admin)
        ->test(Products::class)
        ->call('edit', $product->getKey())
        ->set('form.product_brand_id', $brand->getKey())
        ->call('save')
        ->assertHasNoErrors();

    expect($product->fresh()->product_brand_id)->toBe($brand->getKey());
});

it('denies guests and students from the brands admin', function (): void {
    $this->get(route('admin.product-brands.index'))
        ->assertRedirect(route('login'));

    $student = User::query()->where('email', 'estudiante@open9.dev')->firstOrFail();

    $this->actingAs($student)
        ->get(route('admin.product-brands.index'))
        ->assertForbidden();
});

it('lists only active brands in the public api', function (): void {
    $active = ProductBrand::factory()->create([
        'name' => 'Marca Visible QA',
        'slug' => 'marca-visible-qa',
        'status' => 'active',
    ]);

    ProductBrand::factory()->create([
        'name' => 'Marca Oculta QA',
        'slug' => 'marca-oculta-qa',
        'status' => 'inactive',
    ]);

    $slugs = collect($this->getJson('/api/product-brands')->assertOk()->json('data'))->pluck('slug');

    expect($slugs)->toContain($active->slug)
        ->and($slugs)->not->toContain('marca-oculta-qa')
        ->and($slugs)->toContain('dell');
});

it('includes brand data on published products and filters by slug', function (): void {
    $dell = ProductBrand::query()->where('slug', 'dell')->firstOrFail();
    $product = Product::query()->where('product_brand_id', $dell->id)->where('status', 'published')->firstOrFail();

    $this->getJson('/api/products')
        ->assertOk()
        ->assertJsonFragment([
            'slug' => $product->slug,
            'brand' => 'Dell',
            'brand_slug' => 'dell',
        ]);

    $filtered = collect($this->getJson('/api/products?brand=dell')->assertOk()->json('data'));

    expect($filtered)->not->toBeEmpty()
        ->and($filtered->pluck('brand_slug')->unique()->all())->toBe(['dell']);

    $this->getJson('/api/products/'.$product->slug)
        ->assertOk()
        ->assertJsonPath('brand', 'Dell')
        ->assertJsonPath('brand_slug', 'dell');
});
