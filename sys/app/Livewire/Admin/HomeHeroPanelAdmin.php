<?php

namespace App\Livewire\Admin;

use App\Enums\RecordStatus;
use App\Models\HomeHeroPanelPill;
use App\Models\HomeHeroPanelSetting;
use App\Models\HomeHeroPanelStat;
use App\Services\MediaStorageService;
use App\Services\SiteConfigService;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class HomeHeroPanelAdmin extends Component
{
    use WithFileUploads;

    /** @var array<string, mixed> */
    public array $panel = [];

    public ?TemporaryUploadedFile $imageUpload = null;

    public ?TemporaryUploadedFile $videoUpload = null;

    /** @var array<string, mixed> */
    public array $statForm = [
        'value' => '',
        'suffix' => '',
        'label' => '',
        'sort_order' => 0,
        'is_visible' => true,
        'status' => 'active',
    ];

    /** @var array<string, mixed> */
    public array $pillForm = [
        'label' => '',
        'sort_order' => 0,
        'is_visible' => true,
        'status' => 'active',
    ];

    public ?int $editingStatId = null;

    public ?int $editingPillId = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('home-hero-panel.view'), 403);

        $settings = HomeHeroPanelSetting::query()->firstOrCreate(['id' => 1], $this->defaultPanelSettings());

        $this->panel = $settings->only([
            'badge_label',
            'headline_pre',
            'headline_highlight',
            'headline_subtitle',
            'headline_subtitle_highlight',
            'show_site_name_chip',
            'description',
            'cta_label',
            'cta_url',
            'cta_icon',
            'quote_kicker',
            'quote_primary',
            'quote_secondary',
            'quote_footer',
            'media_type',
            'image_path',
            'video_path',
        ]);
    }

    public function savePanel(): void
    {
        abort_unless(auth()->user()?->can('home-hero-panel.update'), 403);

        $this->validate([
            'panel.badge_label' => ['nullable', 'string', 'max:255'],
            'panel.headline_pre' => ['nullable', 'string', 'max:255'],
            'panel.headline_highlight' => ['nullable', 'string', 'max:255'],
            'panel.headline_subtitle' => ['nullable', 'string', 'max:255'],
            'panel.headline_subtitle_highlight' => ['nullable', 'string', 'max:255'],
            'panel.show_site_name_chip' => ['boolean'],
            'panel.description' => ['nullable', 'string'],
            'panel.cta_label' => ['nullable', 'string', 'max:255'],
            'panel.cta_url' => ['nullable', 'string', 'max:500'],
            'panel.cta_icon' => ['nullable', 'string', 'max:100'],
            'panel.quote_kicker' => ['nullable', 'string', 'max:255'],
            'panel.quote_primary' => ['nullable', 'string', 'max:255'],
            'panel.quote_secondary' => ['nullable', 'string', 'max:255'],
            'panel.quote_footer' => ['nullable', 'string', 'max:255'],
            'panel.media_type' => ['required', 'string', 'in:none,image,video'],
            'imageUpload' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:8192'],
            'videoUpload' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime', 'max:102400'],
        ]);

        $storage = app(MediaStorageService::class);

        if ($this->imageUpload instanceof TemporaryUploadedFile) {
            $this->panel['image_path'] = $storage->store($this->imageUpload, 'home/hero-panel');
            $this->panel['media_type'] = 'image';
        }

        if ($this->videoUpload instanceof TemporaryUploadedFile) {
            $this->panel['video_path'] = $storage->store($this->videoUpload, 'home/hero-panel/videos');
            $this->panel['media_type'] = 'video';
        }

        if (($this->panel['media_type'] ?? 'none') === 'none') {
            $this->panel['image_path'] = null;
            $this->panel['video_path'] = null;
        } elseif ($this->panel['media_type'] === 'image') {
            $this->panel['video_path'] = null;
        } elseif ($this->panel['media_type'] === 'video') {
            $this->panel['image_path'] = null;
        }

        HomeHeroPanelSetting::query()->updateOrCreate(['id' => 1], $this->panel);
        app(SiteConfigService::class)->clearCache();

        $this->imageUpload = null;
        $this->videoUpload = null;

        session()->flash('status', 'Card hero principal actualizado.');
    }

    public function createStat(): void
    {
        abort_unless(auth()->user()?->can('home-hero-panel.create'), 403);
        $this->editingStatId = null;
        $this->statForm = [
            'value' => '',
            'suffix' => '',
            'label' => '',
            'sort_order' => 0,
            'is_visible' => true,
            'status' => 'active',
        ];
    }

    public function editStat(int $id): void
    {
        abort_unless(auth()->user()?->can('home-hero-panel.update'), 403);

        $stat = HomeHeroPanelStat::query()->findOrFail($id);
        $this->editingStatId = $stat->getKey();
        $this->statForm = $stat->only(['value', 'suffix', 'label', 'sort_order', 'is_visible', 'status']);
        $this->statForm['status'] = $stat->status instanceof RecordStatus ? $stat->status->value : (string) $stat->status;
    }

    public function saveStat(): void
    {
        abort_unless(auth()->user()?->can($this->editingStatId ? 'home-hero-panel.update' : 'home-hero-panel.create'), 403);

        $data = $this->validate([
            'statForm.value' => ['required', 'string', 'max:50'],
            'statForm.suffix' => ['nullable', 'string', 'max:50'],
            'statForm.label' => ['required', 'string', 'max:255'],
            'statForm.sort_order' => ['integer', 'min:0'],
            'statForm.is_visible' => ['boolean'],
            'statForm.status' => ['required', 'string', 'in:active,inactive'],
        ])['statForm'];

        $stat = $this->editingStatId
            ? HomeHeroPanelStat::query()->findOrFail($this->editingStatId)
            : new HomeHeroPanelStat;

        $stat->fill($data);
        $stat->save();

        $this->editingStatId = null;
        $this->createStat();
        session()->flash('status', 'Métrica del hero guardada.');
    }

    public function deleteStat(int $id): void
    {
        abort_unless(auth()->user()?->can('home-hero-panel.delete'), 403);
        HomeHeroPanelStat::query()->whereKey($id)->delete();
        session()->flash('status', 'Métrica eliminada.');
    }

    public function createPill(): void
    {
        abort_unless(auth()->user()?->can('home-hero-panel.create'), 403);
        $this->editingPillId = null;
        $this->pillForm = [
            'label' => '',
            'sort_order' => 0,
            'is_visible' => true,
            'status' => 'active',
        ];
    }

    public function editPill(int $id): void
    {
        abort_unless(auth()->user()?->can('home-hero-panel.update'), 403);

        $pill = HomeHeroPanelPill::query()->findOrFail($id);
        $this->editingPillId = $pill->getKey();
        $this->pillForm = $pill->only(['label', 'sort_order', 'is_visible', 'status']);
        $this->pillForm['status'] = $pill->status instanceof RecordStatus ? $pill->status->value : (string) $pill->status;
    }

    public function savePill(): void
    {
        abort_unless(auth()->user()?->can($this->editingPillId ? 'home-hero-panel.update' : 'home-hero-panel.create'), 403);

        $data = $this->validate([
            'pillForm.label' => ['required', 'string', 'max:255'],
            'pillForm.sort_order' => ['integer', 'min:0'],
            'pillForm.is_visible' => ['boolean'],
            'pillForm.status' => ['required', 'string', 'in:active,inactive'],
        ])['pillForm'];

        $pill = $this->editingPillId
            ? HomeHeroPanelPill::query()->findOrFail($this->editingPillId)
            : new HomeHeroPanelPill;

        $pill->fill($data);
        $pill->save();

        $this->editingPillId = null;
        $this->createPill();
        session()->flash('status', 'Etiqueta guardada.');
    }

    public function deletePill(int $id): void
    {
        abort_unless(auth()->user()?->can('home-hero-panel.delete'), 403);
        HomeHeroPanelPill::query()->whereKey($id)->delete();
        session()->flash('status', 'Etiqueta eliminada.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultPanelSettings(): array
    {
        return [
            'badge_label' => 'Expertos en automatización · open9.dev',
            'headline_pre' => 'Expertos en',
            'headline_highlight' => 'automatización',
            'headline_subtitle' => 'Transformamos procesos manuales en',
            'headline_subtitle_highlight' => 'soluciones inteligentes',
            'show_site_name_chip' => true,
            'description' => 'Diseñamos automatizaciones, inteligencia artificial y software a medida que impulsan tu negocio: ahorras tiempo, reduces costos y aumentan resultados.',
            'cta_label' => 'Llevar mi empresa al siguiente nivel',
            'cta_url' => '/contacto',
            'cta_icon' => 'Rocket',
            'quote_kicker' => 'Automatización · IA · Software a medida',
            'quote_primary' => 'Tecnología que impulsa',
            'quote_secondary' => 'tu crecimiento.',
            'quote_footer' => 'www.open9.dev',
            'media_type' => 'none',
        ];
    }

    public function mediaUrl(?string $path): ?string
    {
        return app(MediaStorageService::class)->url($path);
    }

    public function render(): View
    {
        return view('livewire.admin.home-hero-panel', [
            'stats' => HomeHeroPanelStat::query()->orderBy('sort_order')->get(),
            'pills' => HomeHeroPanelPill::query()->orderBy('sort_order')->get(),
        ]);
    }
}
