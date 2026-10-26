<?php

namespace App\Enums;

use App\Enums\Concerns\EnumConEtiqueta;

enum EstadoGeneral: string
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
