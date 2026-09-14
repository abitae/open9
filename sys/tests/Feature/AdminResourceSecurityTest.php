<?php

use App\Livewire\Admin\Clients;
use App\Models\Client;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(DatabaseSeeder::class);
});

it('blocks a user without the clients permission from opening the admin resource page', function (): void {
    $student = User::query()->where('email', 'estudiante@open9.dev')->firstOrFail();

    $this->actingAs($student)
        ->get(route('admin.clients.index'))
        ->assertForbidden();
});

it('resolves options for a declared field but not for an arbitrary/unknown field name', function (): void {
    $admin = User::query()->where('email', 'admin@open9.dev')->firstOrFail();
    $this->actingAs($admin);

    $component = Livewire::test(Clients::class);

    expect($component->instance()->optionsFor('status'))->toBe([
        'active' => 'Activo',
        'inactive' => 'Inactivo',
        'suspended' => 'Suspendido',
    ]);

    // Antes del fix, optionsFor() recibía el array de definición completo
    // desde la vista (incluyendo `options.model`) y podía apuntar a cualquier
    // modelo/columna. Ahora el nombre solo se resuelve contra $fields
    // declarados internamente en el propio recurso, así que un nombre que no
    // sea un select declarado no filtra nada.
    expect($component->instance()->optionsFor('email'))->toBe([]);
    expect($component->instance()->optionsFor('users.password'))->toBe([]);
    expect($component->instance()->optionsFor('no-existe'))->toBe([]);
});

it('ignores a sort request for a column that is not declared for the resource', function (): void {
    Client::factory()->count(2)->create();

    $admin = User::query()->where('email', 'admin@open9.dev')->firstOrFail();
    $this->actingAs($admin);

    Livewire::test(Clients::class)
        ->call('sortBy', 'password')
        ->assertSet('sortField', 'id')
        ->assertSet('sortDirection', 'desc');

    Livewire::test(Clients::class)
        ->call('sortBy', 'status')
        ->assertSet('sortField', 'status')
        ->assertSet('sortDirection', 'asc');
});

it('rejects a status value outside the declared select options when saving a client', function (): void {
    $client = Client::factory()->create(['status' => 'active']);

    $admin = User::query()->where('email', 'admin@open9.dev')->firstOrFail();
    $this->actingAs($admin);

    Livewire::test(Clients::class)
        ->call('edit', $client->id)
        ->set('form.status', 'not-a-real-status')
        ->call('save')
        ->assertHasErrors(['form.status']);

    $this->assertDatabaseHas('clients', ['id' => $client->id, 'status' => 'active']);
});

it('still allows saving a client with a valid declared status', function (): void {
    $client = Client::factory()->create(['status' => 'active']);

    $admin = User::query()->where('email', 'admin@open9.dev')->firstOrFail();
    $this->actingAs($admin);

    Livewire::test(Clients::class)
        ->call('edit', $client->id)
        ->set('form.status', 'suspended')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('clients', ['id' => $client->id, 'status' => 'suspended']);
});
