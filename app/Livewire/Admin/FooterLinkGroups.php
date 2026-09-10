<?php

namespace App\Livewire\Admin;

use App\Models\FooterLinkGroup;

class FooterLinkGroups extends BaseResourceIndex
{
    protected string $modelClass = FooterLinkGroup::class;

    protected string $permission = 'footer-links';

    protected string $title = 'Grupos del footer';

    protected string $description = 'Columnas del pie de página.';

    /** @var array<string, string> */
    protected array $columns = ['id' => 'ID', 'title' => 'Título', 'sort_order' => 'Orden', 'is_visible' => 'Visible'];

    /** @var array<string, array<string, mixed>> */
    protected array $fields = [
        'title' => ['label' => 'Título', 'rules' => ['required', 'string', 'max:255']],
        'sort_order' => ['label' => 'Orden', 'type' => 'number', 'default' => 0, 'rules' => ['integer', 'min:0']],
        'is_visible' => ['label' => 'Visible', 'type' => 'checkbox', 'default' => true, 'rules' => ['boolean']],
    ];
}
