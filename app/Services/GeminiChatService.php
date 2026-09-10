<?php

namespace App\Services;

use App\Models\AiChatSetting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

class GeminiChatService
{
    /**
     * @param  list<array{role: string, text: string}>  $history
     */
    public function chat(string $message, array $history = [], ?AiChatSetting $settings = null): string
    {
        $settings ??= AiChatSetting::query()->firstOrCreate(['id' => 1], [
            'model' => 'gemini-2.0-flash',
            'welcome_message' => 'Hola, soy el asistente de OPEN9.',
        ]);

        if (! $settings->is_enabled) {
            throw new \RuntimeException('El chat no está habilitado.');
        }

        $apiKey = $this->resolveGeminiApiKey($settings);
        $model = $settings->model ?: 'gemini-2.0-flash';

        $contents = [];

        foreach ($history as $item) {
            $contents[] = [
                'role' => $item['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $item['text']]],
            ];
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $message]],
        ];

        $response = Http::timeout(30)
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                'system_instruction' => [
                    'parts' => [['text' => (string) $settings->system_prompt]],
                ],
                'contents' => $contents,
                'generationConfig' => [
                    'temperature' => (float) $settings->temperature,
                    'maxOutputTokens' => (int) $settings->max_tokens,
                ],
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Error al comunicarse con Gemini: '.$response->body());
        }

        $text = data_get($response->json(), 'candidates.0.content.parts.0.text');

        if (! is_string($text) || $text === '') {
            throw new \RuntimeException('Gemini no devolvió una respuesta válida.');
        }

        return trim($text);
    }

    private function resolveGeminiApiKey(AiChatSetting $settings): string
    {
        if ($settings->api_key === null || $settings->api_key === '') {
            throw new \RuntimeException('La API key de Gemini no está configurada.');
        }

        return Crypt::decryptString($settings->api_key);
    }
}
