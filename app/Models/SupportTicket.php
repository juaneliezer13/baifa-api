<?php

namespace App\Models;

use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    use HasFactory;

    /**
     * Atributos asignables de forma masiva.
     *
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'user_id',
        'client_id',
        'assigned_to_user_id',
        'title',
        'category',
        'description',
        'status',
        'final_comment',
        'closed_at',
    ];

    /**
     * Casts de atributos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'closed_at' => 'datetime',
        ];
    }

    /**
     * Usuario (Cliente) creador de la consulta.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Empresa cliente fiscal asociada (si existe).
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Empleado o administrador asignado a la atención del ticket.
     */
    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    /**
     * Mensajes del chat asociados a este ticket.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class, 'ticket_id')->orderBy('created_at', 'asc');
    }

    /**
     * Bitácora histórica y de evolución del ticket.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(TicketLog::class, 'ticket_id')->orderBy('created_at', 'desc');
    }

    /**
     * Scope para filtrar tickets activos (en espera o en proceso).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [TicketStatus::PENDING, TicketStatus::IN_PROGRESS]);
    }

    /**
     * Scope para filtrar tickets finalizados o cerrados.
     */
    public function scopeClosed(Builder $query): Builder
    {
        return $query->whereIn('status', [TicketStatus::FINISHED, TicketStatus::CANCELLED]);
    }

    /**
     * Genera un código correlativo único en formato TKT-YYYY-XXXX.
     */
    public static function generateCode(): string
    {
        $year = date('Y');
        $count = self::whereYear('created_at', $year)->count() + 1;
        $code = sprintf('TKT-%s-%04d', $year, $count);

        while (self::where('code', $code)->exists()) {
            $count++;
            $code = sprintf('TKT-%s-%04d', $year, $count);
        }

        return $code;
    }
}
