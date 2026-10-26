<?php

namespace App\Enums;

use App\Enums\Concerns\EnumConEtiqueta;

/**
 * ActaExamen.estadoActa (CU 21, CU6).
 */
enum EstadoActa: string
{
    use EnumConEtiqueta;

    case Pendiente = 'Pendiente';
    case Finalizado = 'Finalizado';

    public function badge(): string
    {
        return match ($this) {
            self::Pendiente => 'warning',
            self::Finalizado => 'success',
        };
    }
}
