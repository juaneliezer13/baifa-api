<?php

namespace App\Mail;

use App\Models\Client;
use App\Models\Generator;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GeneratorUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $trackingUrl;

    /**
     * @param array<string, array{label: string, old: string, new: string}> $changedFields
     */
    public function __construct(
        public Generator $generator,
        public Client $client,
        public array $changedFields = [],
        public bool $statusChanged = false,
        public ?string $oldStatusLabel = null,
        public ?string $newStatusLabel = null,
        ?string $trackingUrl = null
    ) {
        $baseUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000'));
        $this->trackingUrl = $trackingUrl ?? rtrim($baseUrl, '/') . '/tracking/' . $generator->serial_number;
    }

    public function envelope(): Envelope
    {
        $subject = $this->statusChanged
            ? "[BaiFa Tracking] Cambio de Estatus: Generador #{$this->generator->serial_number} - {$this->newStatusLabel}"
            : "[BaiFa Tracking] Actualización de Información: Generador #{$this->generator->serial_number}";

        return new Envelope(
            from: new Address(
                address: config('mail.from.address', 'chirinosjuane@gmail.com'),
                name: config('mail.from.name', 'BaiFa Power')
            ),
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.generator-updated',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
