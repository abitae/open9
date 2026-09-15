<?php

namespace App\Mail;

use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClientPasswordResetMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Client $client,
        public string $token,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Restablece tu contraseña',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.client-password-reset',
            with: [
                'client' => $this->client,
                'resetUrl' => $this->resetUrl(),
            ],
        );
    }

    public function resetUrl(): string
    {
        $base = rtrim((string) (config('app.url') ?: config('app.frontend_url')), '/');

        return $base.'/restablecer-contrasena?'.http_build_query([
            'token' => $this->token,
            'email' => $this->client->email,
        ]);
    }
}
