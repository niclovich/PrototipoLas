<?php

namespace App\Enums;

use App\Enums\Concerns\EnumConEtiqueta;

/**
 * Correlativa.pCursar / pRendir (CU 8).
 */
enum CondicionCorrelativa: string
{
    use EnumConEtiqueta;

    case Regular = 'Regular';
    case Aprobada = 'Aprobada';

    public function badge(): string
    {
        return match ($this) {
            self::Regular => 'info',
            self::Aprobada => 'success',
        };
    }
}
