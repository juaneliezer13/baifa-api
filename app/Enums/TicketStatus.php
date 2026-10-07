<?php

namespace App\Enums;

enum TicketStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case FINISHED = 'finished';
    case CANCELLED = 'cancelled';

    /**
     * Retorna la etiqueta legible en español para el estado del ticket.
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'En Espera',
            self::IN_PROGRESS => 'En Proceso',
            self::FINISHED => 'Finalizado',
            self::CANCELLED => 'Cancelado',
        };
    }

    /**
     * Retorna el color distintivo acorde al sistema de diseño.
     */
    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'amber',
            self::IN_PROGRESS => 'sky',
            self::FINISHED => 'emerald',
            self::CANCELLED => 'red',
        };
    }

    /**
     * Retorna todos los valores del enum como un array de strings.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
