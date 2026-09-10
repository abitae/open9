<?php

namespace App\Livewire\Admin;

use App\Models\HomeHeroShowcaseCard;
use Illuminate\View\View;

class HomeHeroShowcase extends BaseResourceIndex
{
    protected string $modelClass = HomeHeroShowcaseCard::class;

    protected string $permission = 'home-hero-showcase';

    protected string $title = 'Tarjetas del panel lateral';

    protected string $description = 'Cards del panel derecho del hero (hardware, cloud, software).';

    /** @var array<string, string> */
    protected array $columns = [
        'id' => 'ID',
        'title' => 'Título',
        'layout' => 'Diseño',
        'media_type' => 'Medio',
        'sort_order' => 'Orden',
        'status' => 'Estado',
    ];

    /** @var array<string, array<string, mixed>> */
    protected array $fields = [
        'layout' => [
            'label' => 'Diseño',
            'type' => 'select',
            'default' => 'compact',
            'options' => ['compact' => 'Compacta (2 columnas)', 'featured' => 'Destacada (con imagen o video)'],
            'rules' => ['required', 'string', 'in:compact,featured'],
        ],
        'title' => ['label' => 'Título', 'rules' => ['required', 'string', 'max:255']],
        'description' => ['label' => 'Descripción', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'icon' => ['label' => 'Icono Lucide', 'rules' => ['nullable', 'string', 'max:100']],
        'media_type' => [
            'label' => 'Tipo de medio',
            'type' => 'select',
            'default' => 'none',
            'options' => ['none' => 'Sin medio', 'image' => 'Imagen', 'video' => 'Video'],
            'rules' => ['nullable', 'string', 'in:none,image,video'],
        ],
        'image_path' => [
            'label' => 'Imagen',
            'type' => 'image',
            'directory' => 'home/hero-showcase',
            'rules' => ['nullable', 'string', 'max:255'],
        ],
        'video_path' => [
            'label' => 'Video',
            'type' => 'video',
            'directory' => 'home/hero-showcase/videos',
            'rules' => ['nullable', 'string', 'max:255'],
        ],
        'sort_order' => ['label' => 'Orden', 'type' => 'number', 'default' => 0, 'rules' => ['integer', 'min:0']],
        'is_visible' => ['label' => 'Visible', 'type' => 'checkbox', 'default' => true, 'rules' => ['boolean']],
        'status' => [
            'label' => 'Estado',
            'type' => 'select',
            'default' => 'active',
            'options' => ['active' => 'Activo', 'inactive' => 'Inactivo'],
            'rules' => ['required', 'string'],
        ],
    ];

    public function save(): void
    {
        $this->authorizePermission($this->editingId ? 'update' : 'create');

        $this->normalizeUploadProperties();

        $data = $this->validate()['form'];
        $data = $this->mergeUploadedMedia($data);
        $data = $this->normalizeMediaFields($data);

        $model = $this->editingId
            ? $this->query(includeTrashed: true)->findOrFail($this->editingId)
            : $this->newModel();

        $model->fill($data);
        $model->save();

        $this->showForm = false;
        $this->reset(['editingId', 'form', 'uploads']);
        session()->flash('status', 'Guardado.');
    }

    public function render(): View
    {
        return view('components.admin.partials.resource-index');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function normalizeMediaFields(array $data): array
    {
        if (($data['layout'] ?? 'compact') === 'compact') {
            $data['media_type'] = 'none';
            $data['image_path'] = null;
            $data['video_path'] = null;

            return $data;
        }

        $mediaType = $data['media_type'] ?? 'none';

        if ($mediaType === 'image') {
            $data['video_path'] = null;
        } elseif ($mediaType === 'video') {
            $data['image_path'] = null;
        } else {
            $data['image_path'] = null;
            $data['video_path'] = null;
        }

        return $data;
    }
}
