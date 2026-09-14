<?php

namespace App\Livewire\Admin;

use App\Models\AiChatSetting;
use App\Services\AiChatService;
use App\Services\SiteConfigService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;
use Livewire\Component;

class AiChatSettings extends Component
{
    /** @var array<string, mixed> */
    public array $form = [];

    public string $gemini_api_key = '';

    public string $openai_api_key = '';

    public string $test_message = 'Hola, ¿qué servicios ofrece OPEN9?';

    public ?string $test_reply = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('ai-chat.view'), 403);

        $settings = AiChatSetting::query()->firstOrCreate(['id' => 1], [
            'provider' => 'gemini',
            'welcome_message' => 'Hola, soy el asistente de OPEN9.',
            'model' => 'gemini-2.0-flash',
        ]);

        $this->form = $settings->only([
            'is_enabled', 'provider', 'fab_label', 'welcome_message', 'system_prompt',
            'model', 'temperature', 'max_tokens',
        ]);
    }

    public function updatedFormProvider(): void
    {
        $this->form['model'] = ($this->form['provider'] ?? 'gemini') === 'openai'
            ? 'gpt-4o-mini'
            : 'gemini-2.0-flash';
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->can('ai-chat.update'), 403);

        $this->validate([
            'form.is_enabled' => ['boolean'],
            'form.provider' => ['required', 'string', 'in:gemini,openai'],
            'form.fab_label' => ['required', 'string', 'max:255'],
            'form.welcome_message' => ['required', 'string'],
            'form.system_prompt' => ['nullable', 'string'],
            'form.model' => ['required', 'string', 'max:255'],
            'form.temperature' => ['numeric', 'min:0', 'max:2'],
            'form.max_tokens' => ['integer', 'min:64', 'max:8192'],
            'gemini_api_key' => ['nullable', 'string'],
            'openai_api_key' => ['nullable', 'string'],
        ]);

        $payload = $this->form;

        if ($this->gemini_api_key !== '') {
            $payload['api_key'] = Crypt::encryptString($this->gemini_api_key);
        }

        if ($this->openai_api_key !== '') {
            $payload['openai_api_key'] = Crypt::encryptString($this->openai_api_key);
        }

        AiChatSetting::query()->updateOrCreate(['id' => 1], $payload);
        app(SiteConfigService::class)->clearCache();
        $this->gemini_api_key = '';
        $this->openai_api_key = '';

        session()->flash('status', 'Configuración del chat guardada.');
    }

    public function testConnection(): void
    {
        abort_unless(auth()->user()?->can('ai-chat.update'), 403);

        try {
            $this->test_reply = app(AiChatService::class)->chat($this->test_message);
        } catch (\Throwable $exception) {
            $this->test_reply = 'Error: '.$exception->getMessage();
        }
    }

    public function render(): View
    {
        return view('livewire.admin.ai-chat-settings');
    }
}
