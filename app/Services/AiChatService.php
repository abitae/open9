<?php

namespace App\Services;

use App\Models\AiChatSetting;

class AiChatService
{
    public function __construct(
        private readonly GeminiChatService $gemini,
        private readonly OpenAiChatService $openAi,
    ) {}

    /**
     * @param  list<array{role: string, text: string}>  $history
     */
    public function chat(string $message, array $history = []): string
    {
        $settings = AiChatSetting::query()->firstOrCreate(['id' => 1], [
            'provider' => 'gemini',
            'model' => 'gemini-2.0-flash',
            'welcome_message' => 'Hola, soy el asistente de OPEN9.',
        ]);

        return match ($settings->provider ?? 'gemini') {
            'openai' => $this->openAi->chat($message, $history, $settings),
            default => $this->gemini->chat($message, $history, $settings),
        };
    }
}
