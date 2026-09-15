<?php

namespace App\Mail;

use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClientGooglePasswordHintMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Client $client,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Cómo acceder a tu cuenta',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.client-google-password-hint',
            with: [
                'client' => $this->client,
                'loginUrl' => $this->loginUrl(),
            ],
        );
    }

    public function loginUrl(): string
    {
        $base = rtrim((string) (config('app.url') ?: config('app.frontend_url')), '/');

        return $base.'/ingresar';
    }
}
