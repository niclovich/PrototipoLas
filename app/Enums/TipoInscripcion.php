<?php

namespace App\Enums;

use App\Enums\Concerns\EnumConEtiqueta;

/**
 * AlumnoComision.tipoInscripcion.
 */
enum TipoInscripcion: string
{
    use EnumConEtiqueta;

    case PorComision = 'Por Comisión';

    public function badge(): string
    {
        return match ($this) {
            self::PorComision => 'info',
        };
    }
}
