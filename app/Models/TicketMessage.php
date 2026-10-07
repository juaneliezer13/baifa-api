<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketMessage extends Model
{
    use HasFactory;

    /**
     * Atributos asignables de forma masiva.
     *
     * @var list<string>
     */
    protected $fillable = [
        'ticket_id',
        'user_id',
        'sender_type',
        'message',
    ];

    /**
     * Ticket de soporte al que pertenece el mensaje.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    /**
     * Usuario remitente del mensaje.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
