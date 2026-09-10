<?php

namespace App\Livewire\Admin;

use App\Models\Setting;

class Settings extends BaseResourceIndex
{
    protected string $modelClass = Setting::class;

    protected string $permission = 'settings';

    protected string $title = 'Configuración';

    protected string $description = 'Configuración general.';

    /** @var array<string, string> */
    protected array $columns = ['id' => 'ID', 'group' => 'Grupo', 'key' => 'Clave', 'type' => 'Tipo', 'is_public' => 'Público'];

    /** @var array<string, array<string, mixed>> */
    protected array $fields = [
        'group' => ['label' => 'Grupo', 'rules' => ['required', 'string', 'max:255']],
        'key' => ['label' => 'Clave', 'rules' => ['required', 'string', 'max:255']],
        'value' => ['label' => 'Valor', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'type' => ['label' => 'Tipo', 'type' => 'select', 'default' => 'string', 'options' => ['string' => 'Texto corto', 'text' => 'Texto largo', 'boolean' => 'Booleano', 'image' => 'Imagen', 'json' => 'JSON', 'number' => 'Número'], 'rules' => ['required', 'string']],
        'is_public' => ['label' => 'Público', 'type' => 'checkbox', 'default' => false, 'rules' => ['boolean']],
    ];
}
