<?php

use App\Livewire\Admin\HomeHeroPanelAdmin;
use App\Models\User;
use App\Services\SiteConfigService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('reflects hero panel changes in the home api after saving in admin', function (): void {
    $this->seed(DatabaseSeeder::class);
    app(SiteConfigService::class)->clearCache();

    $admin = User::query()->where('email', 'admin@open9.dev')->firstOrFail();
    $updatedBadge = 'Badge QA '.uniqid();

    $before = $this->getJson('/api/home')->assertOk()->json('hero_panel.badge_label');

    Livewire::actingAs($admin)
        ->test(HomeHeroPanelAdmin::class)
        ->set('panel.badge_label', $updatedBadge)
        ->call('savePanel')
        ->assertHasNoErrors();

    $this->getJson('/api/home')
        ->assertOk()
        ->assertJsonPath('hero_panel.badge_label', $updatedBadge)
        ->assertJsonMissing(['hero_panel' => ['badge_label' => $before]]);
});
