<?php

namespace App\Mail;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketStatusUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $ticketUrl;

    public function __construct(
        public SupportTicket $ticket,
        public string $recipientRole, // 'client' | 'employee'
        public string $oldStatusLabel,
        public string $newStatusLabel,
        public ?string $comment = null,
        public ?string $changedByName = null,
        ?string $ticketUrl = null
    ) {
        $baseUrl = config('app.frontend_url', 'http://localhost:3000');
        $this->ticketUrl = $ticketUrl ?? rtrim($baseUrl, '/') . ($recipientRole === 'client' ? '/my-tickets' : '/tickets');
    }

    public function envelope(): Envelope
    {
        $subject = "[BaiFa Helpdesk] Actualización de Ticket #{$this->ticket->code} — {$this->newStatusLabel}";

        return new Envelope(
            from: new Address(
                address: config('mail.from.address', 'chirinosjuane@gmail.com'),
                name: config('mail.from.name', 'BaiFa Power Helpdesk')
            ),
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-status-updated',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
