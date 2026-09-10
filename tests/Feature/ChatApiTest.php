<?php

use App\Models\AiChatSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('rejects chat requests when the assistant is disabled', function (): void {
    AiChatSetting::query()->updateOrCreate(['id' => 1], [
        'is_enabled' => false,
        'provider' => 'gemini',
    ]);

    $this->postJson('/api/chat', [
        'message' => 'Hola',
    ])
        ->assertUnprocessable()
        ->assertJsonPath('message', 'El chat no está habilitado.');
});

it('rejects chat requests when the provider api key is missing', function (): void {
    AiChatSetting::query()->updateOrCreate(['id' => 1], [
        'is_enabled' => true,
        'provider' => 'gemini',
        'api_key' => null,
        'model' => 'gemini-2.0-flash',
        'system_prompt' => 'Eres el asistente de OPEN9.',
        'temperature' => 0.7,
        'max_tokens' => 256,
    ]);

    $this->postJson('/api/chat', [
        'message' => 'Hola',
    ])
        ->assertUnprocessable()
        ->assertJsonPath('message', 'La API key de Gemini no está configurada.');
});

it('returns assistant replies using a faked provider response', function (): void {
    AiChatSetting::query()->updateOrCreate(['id' => 1], [
        'is_enabled' => true,
        'provider' => 'gemini',
        'api_key' => Crypt::encryptString('test-gemini-key'),
        'model' => 'gemini-2.0-flash',
        'system_prompt' => 'Eres el asistente de OPEN9.',
        'temperature' => 0.7,
        'max_tokens' => 256,
    ]);

    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            ['text' => 'Respuesta fake del asistente OPEN9.'],
                        ],
                    ],
                ],
            ],
        ], 200),
    ]);

    $this->postJson('/api/chat', [
        'message' => 'Hola',
        'history' => [
            ['role' => 'user', 'text' => 'Mensaje previo'],
            ['role' => 'assistant', 'text' => 'Respuesta previa'],
        ],
    ])
        ->assertOk()
        ->assertJsonPath('reply', 'Respuesta fake del asistente OPEN9.');
});
