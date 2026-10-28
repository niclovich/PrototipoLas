<?php

namespace App\Enums;

use App\Enums\Concerns\EnumConEtiqueta;

/**
 * EncuestaSocioeconomica.nivelEstudiosMadre / nivelEstudiosPadre (CU1 P3, decisión D3 de E3).
 * Lista editable: la tesis no la define (plan general §7-12).
 */
enum NivelEstudios: string
{
    use EnumConEtiqueta;

    case SinEstudiosFormales = 'Sin estudios formales';
    case PrimarioIncompleto = 'Primario incompleto';
    case PrimarioCompleto = 'Primario completo';
    case SecundarioIncompleto = 'Secundario incompleto';
    case SecundarioCompleto = 'Secundario completo';
    case TerciarioIncompleto = 'Terciario incompleto';
    case TerciarioCompleto = 'Terciario completo';
    case UniversitarioIncompleto = 'Universitario incompleto';
    case UniversitarioCompleto = 'Universitario completo';

    public function badge(): string
    {
        return 'secondary';
    }
}
