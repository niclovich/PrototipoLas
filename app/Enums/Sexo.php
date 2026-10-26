<?php

namespace App\Enums;

use App\Enums\Concerns\EnumConEtiqueta;

/**
 * Usuario.sexo (valores confirmados por Nicolás).
 */
enum Sexo: string
{
    use EnumConEtiqueta;

    case Femenino = 'Femenino';
    case Masculino = 'Masculino';
    case X = 'X';

    public function badge(): string
    {
        return match ($this) {
            self::Femenino => 'secondary',
            self::Masculino => 'secondary',
            self::X => 'secondary',
        };
    }
}
