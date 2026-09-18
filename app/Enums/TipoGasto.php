<?php

namespace App\Enums;

enum TipoGasto: string
{
    case Combustible = 'combustible';
    case Peaje = 'peaje';
    case Reparacion = 'reparacion';
    case Viatico = 'viatico';
    case Otro = 'otro';

    public function label(): string
    {
        return match ($this) {
            self::Combustible => 'Combustible',
            self::Peaje => 'Peaje',
            self::Reparacion => 'Reparación',
            self::Viatico => 'Viático',
            self::Otro => 'Otro',
        };
    }
}
