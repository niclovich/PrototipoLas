<?php

namespace App\Enums;

use App\Enums\Concerns\EnumConEtiqueta;

/**
 * Usuario.estadoCivil (valores confirmados por Nicolás).
 */
enum EstadoCivil: string
{
    use EnumConEtiqueta;

    case Soltero = 'Soltero/a';
    case Casado = 'Casado/a';
    case Divorciado = 'Divorciado/a';
    case Viudo = 'Viudo/a';
    case UnionConvivencial = 'Unión convivencial';

    public function badge(): string
    {
        return match ($this) {
            self::Soltero => 'secondary',
            self::Casado => 'secondary',
            self::Divorciado => 'secondary',
            self::Viudo => 'secondary',
            self::UnionConvivencial => 'secondary',
        };
    }
}
