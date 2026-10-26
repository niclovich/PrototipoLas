<?php

namespace App\Enums;

use App\Enums\Concerns\EnumConEtiqueta;

/**
 * DetalleActa.condicionFinal (CU6): Aprobado si nota >= 4.
 */
enum CondicionExamen: string
{
    use EnumConEtiqueta;

    case Aprobado = 'Aprobado';
    case Desaprobado = 'Desaprobado';

    public function badge(): string
    {
        return match ($this) {
            self::Aprobado => 'success',
            self::Desaprobado => 'danger',
        };
    }
}
