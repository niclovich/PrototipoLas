<?php

namespace App\Enums;

use App\Enums\Concerns\EnumConEtiqueta;

/**
 * Caja.estadoCaja (CU 35/36). Excepción de género de la §3.
 */
enum EstadoCaja: string
{
    use EnumConEtiqueta;

    case Abierta = 'Abierta';
    case Cerrada = 'Cerrada';

    public function badge(): string
    {
        return match ($this) {
            self::Abierta => 'success',
            self::Cerrada => 'secondary',
        };
    }
}
