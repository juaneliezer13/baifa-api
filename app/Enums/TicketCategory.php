<?php

namespace App\Enums;

enum TicketCategory: string
{
    case TECHNICAL_SUPPORT = 'soporte_tecnico';
    case LOGISTICS = 'logistica';
    case WARRANTY = 'garantias';
    case OTHER = 'otra_consulta';

    /**
     * Retorna la etiqueta legible en español para la categoría de la consulta.
     */
    public function label(): string
    {
        return match ($this) {
            self::TECHNICAL_SUPPORT => 'Soporte Técnico',
            self::LOGISTICS => 'Logística y Despacho',
            self::WARRANTY => 'Garantías y Mantenimiento',
            self::OTHER => 'Otra Consulta',
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
