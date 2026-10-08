<?php

namespace App\Mail;

use App\Enums\GeneratorStatus;
use App\Models\Client;
use App\Models\Checkpoint;
use App\Models\Generator;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GeneratorCheckpointMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $trackingUrl;
    public bool $isArrival;
    public bool $isAvailable;
    public bool $isWarehouse;
    public bool $isInTransit;

    public function __construct(
        public Generator $generator,
        public Checkpoint $checkpoint,
        public Client $client,
        ?string $trackingUrl = null
    ) {
        $baseUrl = config('app.frontend_url', 'http://localhost:3000');
        $this->trackingUrl = $trackingUrl ?? rtrim($baseUrl, '/') . '/tracking?serial=' . urlencode($generator->serial_number);

        $status = $this->checkpoint->status;
        $this->isArrival = ($status === GeneratorStatus::DELIVERED);
        $this->isAvailable = ($status === GeneratorStatus::INSTALLED);
        $this->isWarehouse = ($status === GeneratorStatus::WAREHOUSE);
        $this->isInTransit = ($status === GeneratorStatus::IN_TRANSIT);
    }

    public function envelope(): Envelope
    {
        if ($this->isArrival) {
            $subject = "[BaiFa Tracking] ¡Llegada Confirmada! Generador #{$this->generator->serial_number} - {$this->checkpoint->checkpoint_name}";
        } elseif ($this->isAvailable) {
            $subject = "[BaiFa Tracking] ¡Equipo Disponible y Operativo! Generador #{$this->generator->serial_number}";
        } elseif ($this->isWarehouse) {
            $subject = "[BaiFa Tracking] Generador #{$this->generator->serial_number} en Almacén ({$this->checkpoint->checkpoint_name})";
        } elseif ($this->isInTransit) {
            $subject = "[BaiFa Tracking] En Camino: Generador #{$this->generator->serial_number} - {$this->checkpoint->checkpoint_name}";
        } else {
            $subject = "[BaiFa Tracking] Nuevo Punto de Control: Generador #{$this->generator->serial_number} - {$this->checkpoint->checkpoint_name}";
        }

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
            view: 'emails.generator-checkpoint',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
