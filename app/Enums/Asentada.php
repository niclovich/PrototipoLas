<?php

namespace App\Enums;

use App\Enums\Concerns\EnumConEtiqueta;

/**
 * ActaExamen.asentada (S/N).
 */
enum Asentada: string
{
    use EnumConEtiqueta;

    case Si = 'S';
    case No = 'N';

    public function badge(): string
    {
        return match ($this) {
            self::Si => 'success',
            self::No => 'secondary',
        };
    }
}
