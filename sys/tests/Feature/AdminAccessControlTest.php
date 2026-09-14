<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(DatabaseSeeder::class);
});

it('redirects guests away from the admin dashboard', function (): void {
    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));
});

it('denies students without permissions from the admin dashboard', function (): void {
    $student = User::query()->where('email', 'estudiante@open9.dev')->firstOrFail();

    $this->actingAs($student)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

it('denies students from protected admin resources', function (): void {
    $student = User::query()->where('email', 'estudiante@open9.dev')->firstOrFail();

    $this->actingAs($student)
        ->get(route('admin.contacts.index'))
        ->assertForbidden();
});

it('allows editors to access content modules but not user management', function (): void {
    $editor = User::query()->where('email', 'editor@open9.dev')->firstOrFail();

    $this->actingAs($editor)
        ->get(route('admin.blog.index'))
        ->assertOk();

    $this->actingAs($editor)
        ->get(route('admin.projects.index'))
        ->assertOk();

    $this->actingAs($editor)
        ->get(route('admin.users.index'))
        ->assertForbidden();

    $this->actingAs($editor)
        ->get(route('admin.roles.index'))
        ->assertForbidden();

    $this->actingAs($editor)
        ->get(route('admin.permissions.index'))
        ->assertForbidden();
});

it('allows instructors to access courses but not the store catalog', function (): void {
    $instructor = User::query()->where('email', 'docente@open9.dev')->firstOrFail();

    $this->actingAs($instructor)
        ->get(route('admin.courses.index'))
        ->assertOk();

    $this->actingAs($instructor)
        ->get(route('admin.products.index'))
        ->assertForbidden();
});

it('allows the super admin to access the full admin panel', function (): void {
    $admin = User::query()->where('email', 'admin@open9.dev')->firstOrFail();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('admin.users.index'))
        ->assertOk();
});
