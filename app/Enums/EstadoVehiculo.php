<?php

namespace App\Enums;

enum EstadoVehiculo: string
{
    case Activo = 'activo';
    case Mantenimiento = 'mantenimiento';
    case Inactivo = 'inactivo';

    public function label(): string
    {
        return match ($this) {
            self::Activo => 'Activo',
            self::Mantenimiento => 'En mantenimiento',
            self::Inactivo => 'Inactivo',
        };
    }
}
