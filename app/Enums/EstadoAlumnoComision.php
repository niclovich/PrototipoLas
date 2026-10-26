<?php

namespace App\Enums;

use App\Enums\Concerns\EnumConEtiqueta;

/**
 * AlumnoComision.estado (CU3 y Cambio de Comisión).
 */
enum EstadoAlumnoComision: string
{
    use EnumConEtiqueta;

    case Activo = 'Activo';
    case Caducado = 'Caducado';

    public function badge(): string
    {
        return match ($this) {
            self::Activo => 'success',
            self::Caducado => 'secondary',
        };
    }
}
