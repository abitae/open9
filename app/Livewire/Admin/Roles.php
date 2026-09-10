<?php

namespace App\Livewire\Admin;

use Spatie\Permission\Models\Role;

class Roles extends BaseResourceIndex
{
    protected string $modelClass = Role::class;

    protected string $permission = 'roles';

    protected string $title = 'Roles';

    protected string $description = 'Roles de permisos Spatie.';

    /** @var array<string, string> */
    protected array $columns = ['id' => 'ID', 'name' => 'Nombre', 'guard_name' => 'Guardia'];

    /** @var array<string, array<string, mixed>> */
    protected array $fields = [
        'name' => ['label' => 'Nombre', 'rules' => ['required', 'string', 'max:255'], 'unique' => true],
        'guard_name' => ['label' => 'Guardia', 'default' => 'web', 'rules' => ['required', 'string', 'max:255']],
    ];
}
