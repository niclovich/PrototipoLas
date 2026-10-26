<?php

namespace App\Enums;

use App\Enums\Concerns\EnumConEtiqueta;

/**
 * Estado del legajo del Alumno (por carrera).
 */
enum EstadoLegajo: string
{
    use EnumConEtiqueta;

    case Activo = 'Activo';
    case Cerrado = 'Cerrado';

    public function badge(): string
    {
        return match ($this) {
            self::Activo => 'success',
            self::Cerrado => 'secondary',
        };
    }
}
