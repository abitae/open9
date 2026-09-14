<?php

namespace App\Livewire\Admin;

use App\Models\SocialLoginSetting;
use App\Services\SiteConfigService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;
use Livewire\Component;

class SocialLoginSettings extends Component
{
    /** @var array<string, mixed> */
    public array $form = [];

    public string $google_client_secret = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('social-login.view'), 403);

        $settings = SocialLoginSetting::current();

        $this->form = $settings->only([
            'google_enabled', 'google_client_id', 'google_redirect_url',
        ]);

        if (empty($this->form['google_redirect_url'])) {
            $this->form['google_redirect_url'] = url('/api/auth/google/callback');
        }
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->can('social-login.update'), 403);

        $this->validate([
            'form.google_enabled' => ['boolean'],
            'form.google_client_id' => ['nullable', 'string', 'max:255'],
            'form.google_redirect_url' => ['nullable', 'url', 'max:255'],
            'google_client_secret' => ['nullable', 'string', 'max:255'],
        ]);

        $payload = $this->form;

        if ($this->google_client_secret !== '') {
            $payload['google_client_secret'] = Crypt::encryptString($this->google_client_secret);
        }

        SocialLoginSetting::query()->updateOrCreate(['id' => 1], $payload);
        app(SiteConfigService::class)->clearCache();

        $this->google_client_secret = '';

        session()->flash('status', 'Configuración de acceso con Google guardada.');
    }

    public function render(): View
    {
        return view('livewire.admin.social-login-settings');
    }
}
