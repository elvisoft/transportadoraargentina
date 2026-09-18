<?php

namespace App\Enums;

enum TipoVehiculo: string
{
    case Camion = 'camion';
    case Acoplado = 'acoplado';
    case Semirremolque = 'semirremolque';
    case Utilitario = 'utilitario';
    case Otro = 'otro';

    public function label(): string
    {
        return match ($this) {
            self::Camion => 'Camión',
            self::Acoplado => 'Acoplado',
            self::Semirremolque => 'Semirremolque',
            self::Utilitario => 'Utilitario',
            self::Otro => 'Otro',
        };
    }
}
