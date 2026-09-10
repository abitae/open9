<?php

namespace App\Services;

use App\Models\AiChatSetting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

class OpenAiChatService
{
    /**
     * @param  list<array{role: string, text: string}>  $history
     */
    public function chat(string $message, array $history = [], ?AiChatSetting $settings = null): string
    {
        $settings ??= AiChatSetting::query()->firstOrCreate(['id' => 1], [
            'provider' => 'openai',
            'model' => 'gpt-4o-mini',
            'welcome_message' => 'Hola, soy el asistente de OPEN9.',
        ]);

        if (! $settings->is_enabled) {
            throw new \RuntimeException('El chat no está habilitado.');
        }

        $apiKey = $this->resolveOpenAiApiKey($settings);

        $messages = [];

        if (filled($settings->system_prompt)) {
            $messages[] = ['role' => 'system', 'content' => (string) $settings->system_prompt];
        }

        foreach ($history as $item) {
            $messages[] = [
                'role' => $item['role'] === 'assistant' ? 'assistant' : 'user',
                'content' => $item['text'],
            ];
        }

        $messages[] = ['role' => 'user', 'content' => $message];

        $response = Http::timeout(45)
            ->withToken($apiKey)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $settings->model ?: 'gpt-4o-mini',
                'messages' => $messages,
                'temperature' => (float) $settings->temperature,
                'max_tokens' => (int) $settings->max_tokens,
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Error al comunicarse con OpenAI: '.$response->body());
        }

        $text = data_get($response->json(), 'choices.0.message.content');

        if (! is_string($text) || trim($text) === '') {
            throw new \RuntimeException('OpenAI no devolvió una respuesta válida.');
        }

        return trim($text);
    }

    private function resolveOpenAiApiKey(AiChatSetting $settings): string
    {
        if (filled($settings->openai_api_key)) {
            return Crypt::decryptString($settings->openai_api_key);
        }

        if (filled($settings->api_key) && ($settings->provider ?? 'gemini') === 'openai') {
            return Crypt::decryptString($settings->api_key);
        }

        throw new \RuntimeException('La API key de OpenAI no está configurada.');
    }
}
