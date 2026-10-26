<?php

namespace App\Enums;

use App\Enums\Concerns\EnumConEtiqueta;

/**
 * Estado del Alumno (CU1, CU2, CU6).
 */
enum EstadoAlumno: string
{
    use EnumConEtiqueta;

    case Preinscripto = 'Preinscripto';
    case Inscripto = 'Inscripto';
    case TituloEnTramite = 'Título en Trámite';
    case TituloEntregado = 'Título Entregado';

    public function badge(): string
    {
        return match ($this) {
            self::Preinscripto => 'info',
            self::Inscripto => 'success',
            self::TituloEnTramite => 'primary',
            self::TituloEntregado => 'dark',
        };
    }
}
