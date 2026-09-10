<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeClientMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $userName,
        public string $userEmail
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Bienvenido a Baifa! - Registro exitoso',
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
