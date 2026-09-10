<?php

use App\Models\BlogPost;
use App\Models\Product;
use App\Models\Project;
use App\Services\SiteConfigService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(DatabaseSeeder::class);
    app(SiteConfigService::class)->clearCache();
});

it('exposes site configuration through the public api', function (): void {
    $this->getJson('/api/site')
        ->assertOk()
        ->assertJsonStructure([
            'branding' => ['site_name', 'tagline', 'logo_url', 'favicon_url'],
            'contact' => ['email', 'phone'],
            'footer_groups',
            'chat',
            'payments',
        ]);
});

it('exposes home content through the public api', function (): void {
    $this->getJson('/api/home')
        ->assertOk()
        ->assertJsonStructure([
            'hero_panel' => ['badge_label', 'headline', 'description', 'cta'],
            'hero_showcase',
            'section_headers',
            'stats',
            'feature_cards',
            'workflow_steps',
            'quick_links',
            'pricing_plans',
            'testimonials',
        ]);
});

it('lists published blog posts and hides drafts', function (): void {
    $published = BlogPost::query()->where('status', 'published')->firstOrFail();

    BlogPost::factory()->create([
        'title' => 'Borrador interno QA',
        'slug' => 'borrador-interno-qa',
        'status' => 'draft',
    ]);

    $response = $this->getJson('/api/blog')->assertOk();

    $slugs = collect($response->json('data'))->pluck('slug');

    expect($slugs)->toContain($published->slug)
        ->and($slugs)->not->toContain('borrador-interno-qa');
});

it('returns blog post details and 404 for unknown slugs', function (): void {
    $post = BlogPost::query()->where('status', 'published')->firstOrFail();

    $this->getJson('/api/blog/'.$post->slug)
        ->assertOk()
        ->assertJsonPath('slug', $post->slug)
        ->assertJsonPath('title', $post->title);

    $this->getJson('/api/blog/slug-inexistente-qa')
        ->assertNotFound()
        ->assertJsonPath('message', 'Post no encontrado.');
});

it('lists published projects and hides drafts', function (): void {
    $published = Project::query()->where('status', 'published')->firstOrFail();

    Project::factory()->create([
        'title' => 'Proyecto borrador QA',
        'slug' => 'proyecto-borrador-qa',
        'status' => 'draft',
    ]);

    $response = $this->getJson('/api/projects')->assertOk();

    $slugs = collect($response->json('data'))->pluck('slug');

    expect($slugs)->toContain($published->slug)
        ->and($slugs)->not->toContain('proyecto-borrador-qa');
});

it('returns project details and 404 for unknown slugs', function (): void {
    $project = Project::query()->where('status', 'published')->firstOrFail();

    $this->getJson('/api/projects/'.$project->slug)
        ->assertOk()
        ->assertJsonPath('slug', $project->slug)
        ->assertJsonPath('title', $project->title);

    $this->getJson('/api/projects/slug-inexistente-qa')
        ->assertNotFound()
        ->assertJsonPath('message', 'Proyecto no encontrado.');
});

it('publishes the spanish automation homepage copy from seeders', function (): void {
    $home = $this->getJson('/api/home')->assertOk();

    $home->assertJsonPath('hero_panel.headline.highlight', 'automatización')
        ->assertJsonPath('hero_panel.cta.label', 'Llevar mi empresa al siguiente nivel')
        ->assertJsonPath('section_headers.platform_solutions.title_highlight', 'tu rubro');

    $serviceTitles = collect($home->json('feature_cards'))
        ->where('card_type', 'service')
        ->pluck('title');
    $industryTitles = collect($home->json('feature_cards'))
        ->where('card_type', 'solution')
        ->pluck('title');

    expect($serviceTitles)->toContain('Automatización', 'Inteligencia artificial', 'Chatbots y agentes inteligentes')
        ->and($industryTitles)->toContain('Inmobiliarias', 'Restaurantes', 'Clínicas', 'Comercios', 'Estudios contables');

    $this->getJson('/api/site')
        ->assertOk()
        ->assertJsonPath('contact.email', 'empresario.ia@open9.dev')
        ->assertJsonPath('branding.tagline', 'Expertos en automatización e inteligencia artificial');
});

it('lists published services and products from the seeder', function (): void {
    $response = $this->getJson('/api/services')->assertOk()->assertJsonCount(8, 'data');

    $titles = collect($response->json('data'))->pluck('title');

    expect($titles)->toContain('Automatización de procesos', 'Chatbots y agentes inteligentes');

    $this->getJson('/api/products')
        ->assertOk()
        ->assertJson(fn ($json) => $json->has('data')->etc());

    $this->getJson('/api/product-brands')
        ->assertOk()
        ->assertJson(fn ($json) => $json->has('data.0.slug')->has('data.0.name')->has('data.0.image_url')->etc());
});

it('returns product details and hides unpublished slugs', function (): void {
    $product = Product::query()->where('status', 'published')->firstOrFail();

    $this->getJson('/api/products/'.$product->slug)
        ->assertOk()
        ->assertJsonPath('slug', $product->slug)
        ->assertJsonPath('name', $product->name)
        ->assertJsonStructure(['id', 'slug', 'name', 'prices', 'stock', 'image_url', 'gallery']);

    $this->getJson('/api/products/slug-inexistente-qa')
        ->assertNotFound()
        ->assertJsonPath('message', 'Producto no encontrado.');

    $product->update(['status' => 'draft']);

    $this->getJson('/api/products/'.$product->slug)->assertNotFound();
});

it('serves the public store shell', function (): void {
    $this->get('/tienda')->assertOk();
});

it('serves the public store login at /ingresar', function (): void {
    $this->get('/ingresar')
        ->assertOk()
        ->assertHeader('content-type', 'text/html; charset=UTF-8');
});

it('redirects /login to the public store login', function (): void {
    $this->get('/login')->assertRedirect('/ingresar');
});

it('returns published legal pages and 404 for unknown slugs', function (): void {
    $this->getJson('/api/legal/terminos')
        ->assertOk()
        ->assertJsonPath('slug', 'terminos')
        ->assertJsonPath('title', 'Términos y Condiciones');

    $this->getJson('/api/legal/pagina-inexistente')
        ->assertNotFound()
        ->assertJsonPath('message', 'Página no encontrada.');
});

it('stores public contact messages through the api', function (): void {
    $this->postJson('/api/contact', [
        'name' => 'María QA',
        'email' => 'maria.qa@example.com',
        'phone' => '999888777',
        'company' => 'Open9 QA',
        'message' => 'Consulta desde test automatizado.',
    ])
        ->assertOk()
        ->assertJsonPath('message', 'Mensaje enviado correctamente.');

    $this->assertDatabaseHas('contacts', [
        'email' => 'maria.qa@example.com',
        'status' => 'new',
        'source' => 'web',
    ]);
});
