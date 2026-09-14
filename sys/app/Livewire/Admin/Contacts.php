<?php

namespace App\Livewire\Admin;

use App\Models\Contact;

class Contacts extends BaseResourceIndex
{
    protected string $modelClass = Contact::class;

    protected string $permission = 'contacts';

    protected string $title = 'Contactos';

    protected string $description = 'Mensajes recibidos.';

    /** @var array<string, string> */
    protected array $columns = ['id' => 'ID', 'name' => 'Nombre', 'email' => 'Correo', 'status' => 'Estado'];

    /** @var array<string, array<string, mixed>> */
    protected array $fields = [
        'name' => ['label' => 'Nombre', 'rules' => ['required', 'string', 'max:255']],
        'email' => ['label' => 'Correo', 'type' => 'email', 'rules' => ['required', 'email', 'max:255']],
        'phone' => ['label' => 'Teléfono', 'rules' => ['nullable', 'string', 'max:255']],
        'subject' => ['label' => 'Asunto', 'rules' => ['nullable', 'string', 'max:255']],
        'message' => ['label' => 'Mensaje', 'type' => 'textarea', 'rules' => ['required', 'string']],
        'status' => ['label' => 'Estado', 'type' => 'select', 'default' => 'new', 'options' => ['new' => 'Nuevo', 'read' => 'Leído', 'answered' => 'Respondido', 'archived' => 'Archivado'], 'rules' => ['required', 'string']],
    ];
}
