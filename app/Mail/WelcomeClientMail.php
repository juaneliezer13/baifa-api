<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeClientMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $loginUrl;

    public function __construct(
        public string $userName,
        public string $userEmail,
        public ?string $companyName = null,
        public ?string $rif = null,
        public ?string $initialPassword = null,
        ?string $loginUrl = null
    ) {
        $baseUrl = config('app.frontend_url', 'http://localhost:3000');
        $this->loginUrl = $loginUrl ?? rtrim($baseUrl, '/') . '/login';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                address: config('mail.from.address', 'chirinosjuane@gmail.com'),
                name: config('mail.from.name', 'BaiFa Power')
            ),
            subject: '¡Bienvenido a BaiFa! - Registro de Cliente Exitoso',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome-client',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
