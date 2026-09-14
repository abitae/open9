<?php

use App\Models\BlogPost;
use App\Models\Product;
use App\Models\Project;
use App\Models\Setting;
use App\Services\SiteConfigService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(DatabaseSeeder::class);
    app(SiteConfigService::class)->clearCache();
});

it('paginates the public blog listing', function (): void {
    $response = $this->getJson('/api/blog')->assertOk();

    expect($response->json('data'))->toHaveCount(9);
    expect($response->json('meta.current_page'))->toBe(1);
    expect($response->json('meta.total'))->toBeGreaterThan(9);

    $secondPage = $this->getJson('/api/blog?page=2')->assertOk();

    expect($secondPage->json('meta.current_page'))->toBe(2);
    expect($secondPage->json('data'))->not->toEqual($response->json('data'));
});

it('searches blog posts by title', function (): void {
    BlogPost::query()->first()->update(['title' => 'Automatización única para pruebas QA', 'status' => 'published']);

    $response = $this->getJson('/api/blog?search=pruebas QA')->assertOk();

    expect(collect($response->json('data'))->pluck('title'))->toContain('Automatización única para pruebas QA');
    expect($response->json('meta.total'))->toBe(1);
});

it('paginates the public product listing', function (): void {
    $response = $this->getJson('/api/products')->assertOk();

    expect($response->json('data'))->toHaveCount(12);
    expect($response->json('meta.total'))->toBe(18);
    expect($response->json('meta.last_page'))->toBe(2);
});

it('resolves a one-dollar product by id even when it is off the first catalog page', function (): void {
    $cheap = Product::query()->create([
        'name' => 'Prueba un dólar',
        'slug' => 'prueba-un-dolar-'.uniqid(),
        'description' => 'Producto de prueba a un dólar.',
        'price' => 1,
        'currency' => 'USD',
        'stock' => 10,
        'rating' => 5,
        'sort_order' => 999,
        'status' => 'published',
    ]);

    $pageOneIds = collect($this->getJson('/api/products')->assertOk()->json('data'))->pluck('id');

    expect($pageOneIds)->not->toContain((string) $cheap->id);

    $this->getJson('/api/products?ids='.$cheap->id)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', (string) $cheap->id);

    expect((float) $this->getJson('/api/products?ids='.$cheap->id)->json('data.0.price'))->toBe(1.0);
});

it('hides unpublished products from the cart id lookup', function (): void {
    $product = Product::query()->where('status', 'published')->firstOrFail();
    $product->update(['status' => 'draft']);

    $this->getJson('/api/products?ids='.$product->id)
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

it('lists active blog categories and filters posts by category slug', function (): void {
    $categories = $this->getJson('/api/blog-categories')->assertOk()->json('data');
    expect($categories)->not->toBeEmpty();

    $slug = $categories[0]['slug'];
    $response = $this->getJson('/api/blog?category='.$slug)->assertOk();

    expect(collect($response->json('data')))->not->toBeEmpty();
    expect(collect($response->json('data'))->pluck('category_slug')->unique()->all())->toBe([$slug]);
});

it('searches products by name and can combine it with the brand filter', function (): void {
    $product = Product::query()->where('status', 'published')->firstOrFail();
    $product->update(['name' => 'Servidor Ultra Buscable QA']);

    $response = $this->getJson('/api/products?search=Ultra Buscable')->assertOk();

    expect(collect($response->json('data'))->pluck('slug'))->toContain($product->slug);

    $noMatch = $this->getJson('/api/products?search=Ultra Buscable&brand=no-existe-'.uniqid())->assertOk();

    expect($noMatch->json('data'))->toBeEmpty();
});

it('lists active product categories and filters products by category slug', function (): void {
    $categories = $this->getJson('/api/product-categories')->assertOk()->json('data');
    expect($categories)->not->toBeEmpty();

    $slug = $categories[0]['slug'];
    $response = $this->getJson('/api/products?category='.$slug)->assertOk();

    expect(collect($response->json('data')))->not->toBeEmpty();
    expect(collect($response->json('data'))->pluck('category_slug')->unique()->all())->toBe([$slug]);
});

it('sorts products by price and by name', function (): void {
    $ascending = collect($this->getJson('/api/products?sort=price_asc')->assertOk()->json('data'))->pluck('price');
    expect($ascending->values()->all())->toBe($ascending->sort()->values()->all());

    $descending = collect($this->getJson('/api/products?sort=price_desc')->assertOk()->json('data'))->pluck('price');
    expect($descending->values()->all())->toBe($descending->sortDesc()->values()->all());

    $byName = collect($this->getJson('/api/products?sort=name')->assertOk()->json('data'))->pluck('name');
    expect($byName->values()->all())->toBe($byName->sort(SORT_STRING)->values()->all());
});

it('filters products to only those in stock', function (): void {
    $outOfStock = Product::query()->where('status', 'published')->first();
    $outOfStock->update(['stock' => 0]);

    $result = app(SiteConfigService::class)->products(inStockOnly: true, perPage: 100);

    expect(collect($result['data'])->pluck('slug'))->not->toContain($outOfStock->slug);
    expect($result['meta']['total'])->toBe(17);
});

it('keeps zero-stock products in the available filter when negative stock is allowed', function (): void {
    $outOfStock = Product::query()->where('status', 'published')->firstOrFail();
    $outOfStock->update(['stock' => 0]);

    Setting::query()->updateOrCreate(
        ['group' => 'store', 'key' => 'allow_negative_stock'],
        ['value' => '1', 'type' => 'boolean', 'is_public' => true],
    );
    app(SiteConfigService::class)->clearCache();

    $result = app(SiteConfigService::class)->products(inStockOnly: true, perPage: 100);

    expect(collect($result['data'])->pluck('slug'))->toContain($outOfStock->slug);
});

it('paginates the public projects listing', function (): void {
    $response = $this->getJson('/api/projects')->assertOk();

    expect($response->json('meta.current_page'))->toBe(1);
    expect(count($response->json('data')))->toBeLessThanOrEqual(9);

    $all = $this->getJson('/api/projects?per_page=50')->assertOk();
    expect($all->json('meta.total'))->toBe($response->json('meta.total'));
});

it('searches projects by title and filters by category slug', function (): void {
    $project = Project::query()->where('status', 'published')->firstOrFail();
    $project->update(['title' => 'Proyecto Ultra Buscable QA']);

    $response = $this->getJson('/api/projects?search=Ultra Buscable')->assertOk();
    expect(collect($response->json('data'))->pluck('slug'))->toContain($project->slug);

    $categories = $this->getJson('/api/project-categories')->assertOk()->json('data');
    expect($categories)->not->toBeEmpty();

    $slug = $categories[0]['slug'];
    $filtered = $this->getJson('/api/projects?category='.$slug.'&per_page=50')->assertOk();

    expect(collect($filtered->json('data')))->not->toBeEmpty();
    expect(collect($filtered->json('data'))->pluck('category_slug')->unique()->all())->toBe([$slug]);
});

it('ignores an unknown sort value and falls back to the default order', function (): void {
    $default = $this->getJson('/api/products')->assertOk()->json('data');
    $unknownSort = $this->getJson('/api/products?sort=drop-table')->assertOk()->json('data');

    expect(collect($unknownSort)->pluck('slug'))->toEqual(collect($default)->pluck('slug'));
});
