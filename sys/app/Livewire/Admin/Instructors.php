<?php

namespace App\Livewire\Admin;

use App\Models\Instructor;

class Instructors extends BaseResourceIndex
{
    protected string $modelClass = Instructor::class;

    protected string $permission = 'instructors';

    protected string $title = 'Instructores';

    protected string $description = 'Perfiles docentes.';

    /** @var array<string, string> */
    protected array $columns = ['id' => 'ID', 'name' => 'Nombre', 'email' => 'Correo', 'status' => 'Estado'];

    /** @var array<string, array<string, mixed>> */
    protected array $fields = [
        'name' => ['label' => 'Nombre', 'rules' => ['required', 'string', 'max:255']],
        'email' => ['label' => 'Correo', 'type' => 'email', 'rules' => ['nullable', 'email', 'max:255']],
        'phone' => ['label' => 'Teléfono', 'rules' => ['nullable', 'string', 'max:255']],
        'profession' => ['label' => 'Profesión', 'rules' => ['nullable', 'string', 'max:255']],
        'bio' => ['label' => 'Biografía', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'status' => ['label' => 'Estado', 'type' => 'select', 'default' => 'active', 'options' => ['active' => 'Activo', 'inactive' => 'Inactivo'], 'rules' => ['required', 'string']],
    ];
}
