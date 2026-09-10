<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeUserCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $userName,
        public string $userEmail,
        public string $roleName,
        public string $initialPassword
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu cuenta en Baifa ha sido creada',
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
