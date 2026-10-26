<?php

namespace App\Enums;

use App\Enums\Concerns\EnumConEtiqueta;

/**
 * AlumnoComision.condicionFinal (CU4). Null se muestra "En Curso".
 */
enum CondicionCursada: string
{
    use EnumConEtiqueta;

    /** Etiqueta de solo lectura cuando condicion_final es null (CU4: no la elige el actor). */
    public const EN_CURSO = 'En Curso';

    case Regular = 'Regular';
    case Libre = 'Libre';

    public static function etiqueta(?self $condicion): string
    {
        return $condicion?->label() ?? self::EN_CURSO;
    }

    public function badge(): string
    {
        return match ($this) {
            self::Regular => 'success',
            self::Libre => 'warning',
        };
    }
}
