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
            'google_enabled', 'google_client_id',
        ]);
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->can('social-login.update'), 403);

        $this->validate([
            'form.google_enabled' => ['boolean'],
            'form.google_client_id' => ['nullable', 'string', 'max:255'],
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

    public function liveRedirectUri(): string
    {
        return rtrim(request()->getSchemeAndHttpHost(), '/').'/api/auth/google/callback';
    }

    /**
     * URIs que hay que pegar en Cloud Console. Google compara carácter a carácter
     * con la que envía Laravel (el host real de cada petición).
     *
     * @return list<string>
     */
    public function consoleRedirectUris(): array
    {
        $uris = [
            'https://open9.dev/api/auth/google/callback',
            'https://www.open9.dev/api/auth/google/callback',
            'https://open9.test/api/auth/google/callback',
            $this->liveRedirectUri(),
        ];

        return array_values(array_unique($uris));
    }

    /**
     * @return list<string>
     */
    public function consoleJavaScriptOrigins(): array
    {
        $origins = [
            'https://open9.dev',
            'https://www.open9.dev',
            'https://open9.test',
            rtrim(request()->getSchemeAndHttpHost(), '/'),
        ];

        return array_values(array_unique(array_filter($origins)));
    }

    public function render(): View
    {
        return view('livewire.admin.social-login-settings', [
            'liveRedirectUri' => $this->liveRedirectUri(),
            'consoleRedirectUris' => $this->consoleRedirectUris(),
            'consoleJavaScriptOrigins' => $this->consoleJavaScriptOrigins(),
        ]);
    }
}
