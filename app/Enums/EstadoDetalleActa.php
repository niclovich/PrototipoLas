<?php

namespace App\Enums;

use App\Enums\Concerns\EnumConEtiqueta;

/**
 * DetalleActa.estadoDetalleActa (CU5).
 */
enum EstadoDetalleActa: string
{
    use EnumConEtiqueta;

    case Activo = 'Activo';

    public function badge(): string
    {
        return match ($this) {
            self::Activo => 'success',
        };
    }
}
