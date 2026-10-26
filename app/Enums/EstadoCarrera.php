<?php

namespace App\Enums;

use App\Enums\Concerns\EnumConEtiqueta;

/**
 * Estado de Carrera (CU 3/4).
 */
enum EstadoCarrera: string
{
    use EnumConEtiqueta;

    case Pendiente = 'Pendiente';
    case Activo = 'Activo';
    case Inactivo = 'Inactivo';

    public function badge(): string
    {
        return match ($this) {
            self::Pendiente => 'warning',
            self::Activo => 'success',
            self::Inactivo => 'secondary',
        };
    }
}
