<?php

namespace App\Enums;

enum GeneratorStatus: string
{
    case WAREHOUSE = 'warehouse';
    case IN_TRANSIT = 'in_transit';
    case CHECKPOINT = 'checkpoint';
    case DELIVERED = 'delivered';
    case INSTALLED = 'installed';

    /**
     * Retorna la etiqueta legible en español para el estado del generador.
     */
    public function label(): string
    {
        return match ($this) {
            self::WAREHOUSE => 'En Almacén',
            self::IN_TRANSIT => 'En Tránsito',
            self::CHECKPOINT => 'En Punto de Control',
            self::DELIVERED => 'Entregado en Locación',
            self::INSTALLED => 'Instalado y Operativo',
        };
    }

    /**
     * Retorna el color distintivo acorde al sistema de diseño (Figma).
     */
    public function color(): string
    {
        return match ($this) {
            self::WAREHOUSE => 'violet',
            self::IN_TRANSIT => 'sky',
            self::CHECKPOINT => 'yellow',
            self::DELIVERED => 'green',
            self::INSTALLED => 'emerald',
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
