<?php

use App\Livewire\Admin\SiteBrandingAdmin;
use App\Models\SiteBranding;
use App\Models\User;
use App\Services\SiteConfigService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function (): void {
    Storage::fake('public');
    $this->seed(DatabaseSeeder::class);
});

it('allows uploading logo, dark logo and favicon in site branding', function (): void {
    $admin = User::query()->where('email', 'admin@open9.dev')->firstOrFail();

    Livewire::actingAs($admin)
        ->test(SiteBrandingAdmin::class)
        ->set('logoUpload', UploadedFile::fake()->image('logo.png'))
        ->set('logoDarkUpload', UploadedFile::fake()->image('logo-dark.png'))
        ->set('faviconUpload', UploadedFile::fake()->image('favicon.png', 64, 64))
        ->call('save')
        ->assertHasNoErrors();

    $branding = SiteBranding::query()->findOrFail(1);

    expect($branding->logo_path)->not->toBeEmpty()
        ->and($branding->logo_dark_path)->not->toBeEmpty()
        ->and($branding->favicon_path)->not->toBeEmpty();

    Storage::disk('public')->assertExists($branding->logo_path);
    Storage::disk('public')->assertExists($branding->logo_dark_path);
    Storage::disk('public')->assertExists($branding->favicon_path);
});

it('allows clearing site name without database errors', function (): void {
    $admin = User::query()->where('email', 'admin@open9.dev')->firstOrFail();

    Livewire::actingAs($admin)
        ->test(SiteBrandingAdmin::class)
        ->set('form.site_name', '')
        ->call('save')
        ->assertHasNoErrors();

    expect(SiteBranding::query()->findOrFail(1)->site_name)->toBe('');
});

it('uses the public brand logos by default in the site api payload', function (): void {
    app(SiteConfigService::class)->clearCache();

    $response = $this->getJson('/api/site');

    $response->assertOk()
        ->assertJsonPath('branding.logo_url', fn (?string $url): bool => str_contains((string) $url, 'logo_normal.png'))
        ->assertJsonPath('branding.logo_dark_url', fn (?string $url): bool => str_contains((string) $url, 'logo_black.png'))
        ->assertJsonPath('branding.favicon_url', fn (?string $url): bool => str_contains((string) $url, 'favicon.png'));
});

it('falls back to public brand logos when custom paths are empty', function (): void {
    SiteBranding::query()->whereKey(1)->update([
        'logo_path' => null,
        'logo_dark_path' => null,
        'favicon_path' => null,
    ]);

    app(SiteConfigService::class)->clearCache();

    $response = $this->getJson('/api/site');

    $response->assertOk()
        ->assertJsonPath('branding.logo_url', fn (?string $url): bool => str_contains((string) $url, 'logo_normal.png'))
        ->assertJsonPath('branding.logo_dark_url', fn (?string $url): bool => str_contains((string) $url, 'logo_black.png'))
        ->assertJsonPath('branding.favicon_url', fn (?string $url): bool => str_contains((string) $url, 'favicon.png'));
});

it('exposes uploaded branding assets in the site api payload', function (): void {
    $admin = User::query()->where('email', 'admin@open9.dev')->firstOrFail();

    Livewire::actingAs($admin)
        ->test(SiteBrandingAdmin::class)
        ->set('logoUpload', UploadedFile::fake()->image('logo.png'))
        ->set('logoDarkUpload', UploadedFile::fake()->image('logo-dark.png'))
        ->set('faviconUpload', UploadedFile::fake()->image('favicon.png', 32, 32))
        ->call('save')
        ->assertHasNoErrors();

    $response = $this->getJson('/api/site');

    $response->assertOk()
        ->assertJsonPath('branding.logo_url', fn (?string $url): bool => filled($url))
        ->assertJsonPath('branding.logo_dark_url', fn (?string $url): bool => filled($url))
        ->assertJsonPath('branding.favicon_url', fn (?string $url): bool => filled($url));
});
