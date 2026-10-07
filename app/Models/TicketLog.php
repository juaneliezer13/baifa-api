<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketLog extends Model
{
    use HasFactory;

    /**
     * Esta tabla sólo tiene created_at (sin updated_at).
     */
    public $timestamps = false;

    /**
     * Atributos asignables de forma masiva.
     *
     * @var list<string>
     */
    protected $fillable = [
        'ticket_id',
        'user_id',
        'action',
        'previous_status',
        'new_status',
        'comment',
        'metadata',
        'created_at',
    ];

    /**
     * Casts de atributos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Ticket de soporte al que pertenece el registro de bitácora.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    /**
     * Usuario que efectuó la acción (null si fue una acción automática del sistema).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
