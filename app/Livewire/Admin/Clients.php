<?php

namespace App\Livewire\Admin;

use App\Models\Client;

class Clients extends BaseResourceIndex
{
    protected string $modelClass = Client::class;

    protected string $permission = 'clients';

    protected string $title = 'Clientes';

    protected string $description = 'Cuentas de la tienda: registro, contacto y estado.';

    /** @var array<string, string> */
    protected array $columns = ['id' => 'ID', 'name' => 'Nombre', 'email' => 'Correo', 'phone' => 'Teléfono', 'status' => 'Estado'];

    /** @var array<string, array<string, mixed>> */
    protected array $fields = [
        'name' => ['label' => 'Nombre', 'rules' => ['required', 'string', 'max:255']],
        'email' => ['label' => 'Correo', 'type' => 'email', 'rules' => ['required', 'email', 'max:255'], 'unique' => true],
        'phone' => ['label' => 'Teléfono', 'rules' => ['nullable', 'string', 'max:255']],
        'status' => ['label' => 'Estado', 'type' => 'select', 'default' => 'active', 'options' => ['active' => 'Activo', 'inactive' => 'Inactivo', 'suspended' => 'Suspendido'], 'rules' => ['required', 'string']],
    ];
}
