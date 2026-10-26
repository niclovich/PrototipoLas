<?php

namespace App\Enums;

use App\Enums\Concerns\EnumConEtiqueta;

enum EstadoCuenta: string
{
    use EnumConEtiqueta;

    case Activo = 'Activo';
    case Inactivo = 'Inactivo';

    public function badge(): string
    {
        return match ($this) {
            self::Activo => 'success',
            self::Inactivo => 'secondary',
        };
    }
}
