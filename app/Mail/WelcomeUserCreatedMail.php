<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeUserCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $loginUrl;

    public function __construct(
        public string $userName,
        public string $userEmail,
        public string $roleName,
        public string $initialPassword,
        ?string $loginUrl = null
    ) {
        $baseUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000'));
        $this->loginUrl = $loginUrl ?? rtrim($baseUrl, '/') . '/login';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                address: config('mail.from.address', 'chirinosjuane@gmail.com'),
                name: config('mail.from.name', 'BaiFa Power')
            ),
            subject: 'Tu cuenta en BaiFa ha sido creada exitosamente',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome-user-created',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
