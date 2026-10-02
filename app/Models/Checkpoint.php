<?php

namespace App\Models;

use App\Enums\GeneratorStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Checkpoint extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'generator_id',
        'user_id',
        'status',
        'checkpoint_name',
        'event_date',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => GeneratorStatus::class,
            'event_date' => 'datetime',
        ];
    }

    /**
     * Generador asociado a este punto de control.
     */
    public function generator(): BelongsTo
    {
        return $this->belongsTo(Generator::class);
    }

    /**
     * Usuario (operador o administrador) que registró este punto de control.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Etiqueta amigable en español del estado en este checkpoint.
     */
    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status instanceof GeneratorStatus ? $this->status->label() : $this->status
        );
    }

    /**
     * Color asignado al estado según el sistema de diseño Figma.
     */
    protected function statusColor(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status instanceof GeneratorStatus ? $this->status->color() : 'slate'
        );
    }
}
