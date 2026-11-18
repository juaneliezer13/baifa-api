<?php

namespace App\Enums;

enum GeneratorStatus: string
{
    case PURCHASE_ORDER = 'purchase_order';
    case IN_PRODUCTION = 'in_production';
    case ORIGIN_CUSTOMS = 'origin_customs';
    case MARITIME_TRANSIT = 'maritime_transit';
    case DESTINATION_CUSTOMS = 'destination_customs';
    case BAIFA_WAREHOUSE = 'baifa_warehouse';
    case DELIVERED = 'delivered';

    /**
     * Retorna la etiqueta legible en español para el estado del generador.
     */
    public function label(): string
    {
        return match ($this) {
            self::PURCHASE_ORDER => 'Orden de Compra',
            self::IN_PRODUCTION => 'En Producción',
            self::ORIGIN_CUSTOMS => 'En Aduana de Origen',
            self::MARITIME_TRANSIT => 'En Tránsito Marítimo',
            self::DESTINATION_CUSTOMS => 'En Aduana Destino',
            self::BAIFA_WAREHOUSE => 'En Almacén BaiFa',
            self::DELIVERED => 'Entregado',
        };
    }

    /**
     * Retorna el color distintivo acorde al sistema de diseño (Figma).
     */
    public function color(): string
    {
        return match ($this) {
            self::PURCHASE_ORDER => 'violet',
            self::IN_PRODUCTION => 'amber',
            self::ORIGIN_CUSTOMS => 'orange',
            self::MARITIME_TRANSIT => 'sky',
            self::DESTINATION_CUSTOMS => 'yellow',
            self::BAIFA_WAREHOUSE => 'cyan',
            self::DELIVERED => 'emerald',
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
