<?php

namespace App\Enums;

use App\Enums\Concerns\EnumConEtiqueta;

/**
 * EstadoCurricular.condicion (CU4, CU6).
 */
enum CondicionCurricular: string
{
    use EnumConEtiqueta;

    case Regular = 'Regular';
    case Libre = 'Libre';
    case Aprobado = 'Aprobado';
    case Desaprobado = 'Desaprobado';

    public function badge(): string
    {
        return match ($this) {
            self::Regular => 'info',
            self::Libre => 'warning',
            self::Aprobado => 'success',
            self::Desaprobado => 'danger',
        };
    }
}
