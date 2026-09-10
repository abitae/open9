<?php

namespace App\Livewire\Admin;

use App\Models\SocialLink;

class SocialLinks extends BaseResourceIndex
{
    protected string $modelClass = SocialLink::class;

    protected string $permission = 'social-links';

    protected string $title = 'Redes sociales';

    protected string $description = 'Enlaces a redes sociales del sitio.';

    /** @var array<string, string> */
    protected array $columns = ['id' => 'ID', 'platform' => 'Plataforma', 'url' => 'URL', 'sort_order' => 'Orden'];

    /** @var array<string, array<string, mixed>> */
    protected array $fields = [
        'platform' => [
            'label' => 'Plataforma',
            'type' => 'select',
            'options' => [
                'twitter' => 'Twitter/X',
                'linkedin' => 'LinkedIn',
                'instagram' => 'Instagram',
                'facebook' => 'Facebook',
                'youtube' => 'YouTube',
            ],
            'rules' => ['required', 'string', 'max:255'],
        ],
        'url' => ['label' => 'URL', 'rules' => ['required', 'url', 'max:500']],
        'sort_order' => ['label' => 'Orden', 'type' => 'number', 'default' => 0, 'rules' => ['integer', 'min:0']],
        'is_visible' => ['label' => 'Visible', 'type' => 'checkbox', 'default' => true, 'rules' => ['boolean']],
    ];
}
