<?php

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('seeds the demo admin with the super-admin role on first run', function (): void {
    $this->seed(DatabaseSeeder::class);

    $admin = User::query()->where('email', 'admin@open9.dev')->firstOrFail();
    expect($admin->hasRole('super-admin'))->toBeTrue();
});

it('does not overwrite an existing admin password when the seeder runs again', function (): void {
    $this->seed(DatabaseSeeder::class);

    $admin = User::query()->where('email', 'admin@open9.dev')->firstOrFail();
    $admin->forceFill(['password' => Hash::make('a-different-password-set-by-the-owner')])->save();

    // Se instancia y ejecuta directamente (en vez de `$this->seed()`, que pasa
    // por el comando Artisan `db:seed` y sus prompts de confirmación) para
    // aislar exactamente la lógica de AdminUserSeeder::run().
    (new AdminUserSeeder)->run();

    $admin->refresh();
    expect(Hash::check('a-different-password-set-by-the-owner', $admin->password))->toBeTrue();
});

it('does nothing outside local/testing environments', function (): void {
    app()->instance('env', 'production');

    (new AdminUserSeeder)->run();

    $this->assertDatabaseMissing('users', ['email' => 'admin@open9.dev']);

    app()->instance('env', 'testing');
});
