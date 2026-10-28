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

    /** "Si" / "No" (columna Asentada de P1 Gestión de Actas). */
    public function label(): string
    {
        return $this->name;
    }

    public function badge(): string
    {
        return match ($this) {
            self::Si => 'success',
            self::No => 'secondary',
        };
    }
}
