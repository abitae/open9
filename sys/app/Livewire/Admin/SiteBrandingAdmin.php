<?php

namespace App\Livewire\Admin;

use App\Models\SiteBranding;
use App\Services\MediaStorageService;
use App\Services\SiteConfigService;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class SiteBrandingAdmin extends Component
{
    use WithFileUploads;

    /** @var array<string, mixed> */
    public array $form = [];

    public ?TemporaryUploadedFile $logoUpload = null;

    public ?TemporaryUploadedFile $logoDarkUpload = null;

    public ?TemporaryUploadedFile $faviconUpload = null;

    public ?TemporaryUploadedFile $backgroundVideoUpload = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('site-branding.view'), 403);

        $branding = SiteBranding::query()->firstOrCreate(['id' => 1], [
            'site_name' => 'Open9',
        ]);

        $this->form = $branding->only([
            'site_name', 'tagline',
            'background_video_url', 'contact_email', 'contact_phone',
            'contact_address', 'website_url', 'footer_description',
            'copyright_text', 'seo_description', 'logo_path', 'logo_dark_path', 'favicon_path',
        ]);
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->can('site-branding.update'), 403);

        $this->validate([
            'form.site_name' => ['nullable', 'string', 'max:255'],
            'form.tagline' => ['nullable', 'string', 'max:255'],
            'form.background_video_url' => ['nullable', 'string', 'max:500'],
            'form.contact_email' => ['nullable', 'email', 'max:255'],
            'form.contact_phone' => ['nullable', 'string', 'max:255'],
            'form.contact_address' => ['nullable', 'string', 'max:500'],
            'form.website_url' => ['nullable', 'url', 'max:500'],
            'form.footer_description' => ['nullable', 'string'],
            'form.copyright_text' => ['nullable', 'string', 'max:255'],
            'form.seo_description' => ['nullable', 'string'],
            'logoUpload' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,svg', 'max:4096'],
            'logoDarkUpload' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,svg', 'max:4096'],
            'faviconUpload' => ['nullable', 'file', 'mimes:ico,png,svg,webp,jpg,jpeg', 'max:2048'],
            'backgroundVideoUpload' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime', 'max:102400'],
        ]);

        $storage = app(MediaStorageService::class);

        if ($this->logoUpload instanceof TemporaryUploadedFile) {
            $this->form['logo_path'] = $storage->store($this->logoUpload, 'branding/logo');
        }

        if ($this->logoDarkUpload instanceof TemporaryUploadedFile) {
            $this->form['logo_dark_path'] = $storage->store($this->logoDarkUpload, 'branding/logo-dark');
        }

        if ($this->faviconUpload instanceof TemporaryUploadedFile) {
            $this->form['favicon_path'] = $storage->store($this->faviconUpload, 'branding/favicon');
        }

        if ($this->backgroundVideoUpload instanceof TemporaryUploadedFile) {
            $this->form['background_video_url'] = $storage->store($this->backgroundVideoUpload, 'branding/background');
        }

        $this->form['site_name'] = trim((string) ($this->form['site_name'] ?? ''));

        SiteBranding::query()->updateOrCreate(['id' => 1], $this->form);
        app(SiteConfigService::class)->clearCache();

        $this->logoUpload = null;
        $this->logoDarkUpload = null;
        $this->faviconUpload = null;
        $this->backgroundVideoUpload = null;

        session()->flash('status', 'Identidad del sitio actualizada. Los cambios se reflejan en el frontend público.');
    }

    public function mediaUrl(?string $path): ?string
    {
        return app(MediaStorageService::class)->url($path);
    }

    public function frontendUrl(): string
    {
        return rtrim((string) (config('app.frontend_url') ?: config('app.url')), '/');
    }

    /**
     * @return list<array{label: string, route: string|null}>
     */
    public function relatedModules(): array
    {
        $modules = [
            ['label' => 'Hero — card principal', 'route' => 'admin.home-hero-panel.index'],
            ['label' => 'Footer — grupos de enlaces', 'route' => 'admin.footer-link-groups.index'],
            ['label' => 'Footer — enlaces', 'route' => 'admin.footer-links.index'],
            ['label' => 'Redes sociales', 'route' => 'admin.social-links.index'],
            ['label' => 'Chat IA', 'route' => 'admin.ai-chat.index'],
        ];

        return collect($modules)
            ->filter(fn (array $module): bool => $module['route'] !== null && Route::has($module['route']))
            ->values()
            ->all();
    }

    public function render(): View
    {
        return view('livewire.admin.site-branding');
    }
}
