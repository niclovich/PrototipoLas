<?php

namespace App\Enums;

use App\Enums\Concerns\EnumConEtiqueta;

/**
 * Alumno.situacionEstudios.
 */
enum SituacionEstudios: string
{
    use EnumConEtiqueta;

    case Completo = 'Completo';
    case EnCurso = 'En curso';
    case Incompleto = 'Incompleto';

    public function badge(): string
    {
        return match ($this) {
            self::Completo => 'success',
            self::EnCurso => 'info',
            self::Incompleto => 'warning',
        };
    }
}
