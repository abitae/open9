<?php

namespace App\Livewire\Admin;

use App\Models\User;

class Users extends BaseResourceIndex
{
    protected string $modelClass = User::class;

    protected string $permission = 'users';

    protected string $title = 'Usuarios';

    protected string $description = 'Cuentas y estado de acceso.';

    /** @var array<string, string> */
    protected array $columns = ['id' => 'ID', 'name' => 'Nombre', 'email' => 'Correo', 'status' => 'Estado'];

    /** @var array<string, array<string, mixed>> */
    protected array $fields = [
        'name' => ['label' => 'Nombre', 'rules' => ['required', 'string', 'max:255']],
        'email' => ['label' => 'Correo', 'type' => 'email', 'rules' => ['required', 'email', 'max:255'], 'unique' => true],
        'phone' => ['label' => 'Teléfono', 'rules' => ['nullable', 'string', 'max:255']],
        'status' => ['label' => 'Estado', 'type' => 'select', 'default' => 'active', 'options' => ['active' => 'Activo', 'inactive' => 'Inactivo', 'suspended' => 'Suspendido'], 'rules' => ['required', 'string']],
    ];
}
