<?php

use App\Livewire\Admin\StoreSettings;
use App\Models\User;
use App\Services\SiteConfigService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(DatabaseSeeder::class);
    app(SiteConfigService::class)->clearCache();
});

it('exposes the negative stock policy in the public site payload', function (): void {
    $this->getJson('/api/site')
        ->assertOk()
        ->assertJsonPath('store.allow_negative_stock', false);
});

it('lets an admin enable negative stock', function (): void {
    $admin = User::query()->where('email', 'admin@open9.dev')->firstOrFail();

    $this->actingAs($admin);

    Livewire::test(StoreSettings::class)
        ->set('allow_negative_stock', true)
        ->call('save')
        ->assertHasNoErrors();

    expect(app(SiteConfigService::class)->allowsNegativeStock())->toBeTrue();

    $this->getJson('/api/site')
        ->assertOk()
        ->assertJsonPath('store.allow_negative_stock', true);
});

it('forbids students from the store inventory settings', function (): void {
    $student = User::query()->where('email', 'estudiante@open9.dev')->firstOrFail();

    $this->actingAs($student)
        ->get(route('admin.store-settings.index'))
        ->assertForbidden();
});
