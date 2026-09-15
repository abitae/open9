<?php

use App\Livewire\Admin\SocialLoginSettings;
use App\Models\SocialLoginSetting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(DatabaseSeeder::class);
});

it('shows the live callback uri and the console uris to paste in google', function (): void {
    $admin = User::query()->where('email', 'admin@open9.dev')->firstOrFail();

    $this->actingAs($admin);

    Livewire::test(SocialLoginSettings::class)
        ->assertSee('https://open9.dev/api/auth/google/callback', false)
        ->assertSee('https://www.open9.dev/api/auth/google/callback', false)
        ->assertSee('https://open9.test/api/auth/google/callback', false)
        ->assertSee('https://open9.dev', false)
        ->assertSee('URI que envía este host ahora', false)
        ->assertDontSee('wire:model="form.google_redirect_url"', false);
});

it('does not overwrite google_redirect_url when saving credentials', function (): void {
    SocialLoginSetting::query()->updateOrCreate(['id' => 1], [
        'google_enabled' => false,
        'google_client_id' => 'old-id',
        'google_redirect_url' => 'https://old.example/api/auth/google/callback',
    ]);

    $admin = User::query()->where('email', 'admin@open9.dev')->firstOrFail();

    $this->actingAs($admin);

    Livewire::test(SocialLoginSettings::class)
        ->set('form.google_enabled', true)
        ->set('form.google_client_id', 'new-id.apps.googleusercontent.com')
        ->call('save')
        ->assertHasNoErrors();

    $settings = SocialLoginSetting::query()->findOrFail(1);

    expect($settings->google_enabled)->toBeTrue()
        ->and($settings->google_client_id)->toBe('new-id.apps.googleusercontent.com')
        ->and($settings->google_redirect_url)->toBe('https://old.example/api/auth/google/callback');
});

it('forbids students from the google login settings', function (): void {
    $student = User::query()->where('email', 'estudiante@open9.dev')->firstOrFail();

    $this->actingAs($student)
        ->get(route('admin.social-login.index'))
        ->assertForbidden();
});
