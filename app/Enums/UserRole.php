<?php

namespace App\Enums;

enum UserRole: string
{
    case CLIENT = 'client';
    case EMPLOYEE = 'employee';
    case MANAGER = 'manager';
    case ADMIN = 'admin';

    /**
     * Retorna la etiqueta legible en español para el rol.
     */
    public function label(): string
    {
        return match ($this) {
            self::CLIENT => 'Cliente',
            self::EMPLOYEE => 'Empleado',
            self::MANAGER => 'Jefe / Gerente',
            self::ADMIN => 'Administrador',
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
