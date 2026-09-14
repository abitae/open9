<?php

namespace App\Livewire\Admin;

use App\Models\NewsletterSubscriber;

class Newsletter extends BaseResourceIndex
{
    protected string $modelClass = NewsletterSubscriber::class;

    protected string $permission = 'newsletter';

    protected string $title = 'Boletín';

    protected string $description = 'Lista de suscriptores.';

    /** @var array<string, string> */
    protected array $columns = ['id' => 'ID', 'email' => 'Correo', 'name' => 'Nombre', 'status' => 'Estado'];

    /** @var array<string, array<string, mixed>> */
    protected array $fields = [
        'email' => ['label' => 'Correo', 'type' => 'email', 'rules' => ['required', 'email', 'max:255'], 'unique' => true],
        'name' => ['label' => 'Nombre', 'rules' => ['nullable', 'string', 'max:255']],
        'status' => ['label' => 'Estado', 'type' => 'select', 'default' => 'active', 'options' => ['active' => 'Activo', 'unsubscribed' => 'Desuscrito'], 'rules' => ['required', 'string']],
    ];
}
