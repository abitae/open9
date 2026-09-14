<?php

namespace App\Livewire\Admin;

use Spatie\Permission\Models\Permission;

class Permissions extends BaseResourceIndex
{
    protected string $modelClass = Permission::class;

    protected string $permission = 'permissions';

    protected string $title = 'Permisos';

    protected string $description = 'Catálogo de permisos Spatie.';

    /** @var array<string, string> */
    protected array $columns = ['id' => 'ID', 'name' => 'Nombre', 'guard_name' => 'Guardia'];

    /** @var array<string, array<string, mixed>> */
    protected array $fields = [
        'name' => ['label' => 'Nombre', 'rules' => ['required', 'string', 'max:255'], 'unique' => true],
        'guard_name' => ['label' => 'Guardia', 'default' => 'web', 'rules' => ['required', 'string', 'max:255']],
    ];
}
