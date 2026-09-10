<?php

namespace App\Livewire\Admin;

use App\Models\HomeSectionSetting;

class HomeSectionSettings extends BaseResourceIndex
{
    protected string $modelClass = HomeSectionSetting::class;

    protected string $permission = 'home-section-headers';

    protected string $title = 'Encabezados de secciones';

    protected string $description = 'Títulos y textos introductorios de cada sección del home y CTA de contacto.';

    /** @var array<string, string> */
    protected array $columns = [
        'section_key' => 'Sección',
        'label' => 'Etiqueta',
        'title' => 'Título',
        'is_visible' => 'Visible',
    ];

    /** @var list<string> */
    protected array $searchable = ['section_key', 'label', 'title'];

    /** @var array<string, array<string, mixed>> */
    protected array $fields = [
        'section_key' => [
            'label' => 'Clave de sección',
            'rules' => ['required', 'string', 'max:100'],
            'detailOnly' => true,
        ],
        'label' => ['label' => 'Etiqueta superior', 'rules' => ['nullable', 'string', 'max:255']],
        'title' => ['label' => 'Título', 'rules' => ['nullable', 'string', 'max:255']],
        'title_highlight' => ['label' => 'Título — parte destacada (cursiva)', 'rules' => ['nullable', 'string', 'max:255']],
        'description' => ['label' => 'Descripción', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'cta_label' => ['label' => 'CTA — etiqueta', 'rules' => ['nullable', 'string', 'max:255']],
        'cta_url' => ['label' => 'CTA — URL', 'rules' => ['nullable', 'string', 'max:500']],
        'sort_order' => ['label' => 'Orden', 'type' => 'number', 'default' => 0, 'rules' => ['integer', 'min:0']],
        'is_visible' => ['label' => 'Visible', 'type' => 'checkbox', 'default' => true, 'rules' => ['boolean']],
    ];

    public function create(): void
    {
        session()->flash('status', 'Las secciones son fijas. Edita una existente o ejecuta el seeder del CMS.');
    }

    /**
     * @return array<string, mixed>
     */
    public function meta(): array
    {
        $meta = parent::meta();
        $meta['canCreate'] = false;

        return $meta;
    }
}
