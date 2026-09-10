<?php

namespace App\Livewire\Admin;

use App\Models\FooterLink;

class FooterLinks extends BaseResourceIndex
{
    protected string $modelClass = FooterLink::class;

    protected string $permission = 'footer-links';

    protected string $title = 'Enlaces del footer';

    protected string $description = 'Enlaces dentro de cada columna.';

    /** @var list<string> */
    protected array $with = ['group'];

    /** @var array<string, string> */
    protected array $columns = ['id' => 'ID', 'label' => 'Etiqueta', 'url' => 'URL', 'sort_order' => 'Orden'];

    /** @var array<string, array<string, mixed>> */
    protected array $fields = [
        'footer_link_group_id' => [
            'label' => 'Grupo',
            'type' => 'select',
            'options' => ['model' => \App\Models\FooterLinkGroup::class, 'label' => 'title'],
            'rules' => ['required', 'integer', 'exists:footer_link_groups,id'],
        ],
        'label' => ['label' => 'Etiqueta', 'rules' => ['required', 'string', 'max:255']],
        'url' => ['label' => 'URL', 'rules' => ['required', 'string', 'max:500']],
        'is_external' => ['label' => 'Externo', 'type' => 'checkbox', 'default' => false, 'rules' => ['boolean']],
        'sort_order' => ['label' => 'Orden', 'type' => 'number', 'default' => 0, 'rules' => ['integer', 'min:0']],
    ];
}
