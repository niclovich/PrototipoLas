<?php

namespace App\Enums;

use App\Enums\Concerns\EnumConEtiqueta;

/**
 * TituloTramite.estadoTramite (CU6 crea "Sin Gestión").
 */
enum EstadoTramite: string
{
    use EnumConEtiqueta;

    case SinGestion = 'Sin Gestión';
    case Iniciado = 'Iniciado';
    case EnInstituto = 'En Instituto';
    case Retirado = 'Retirado';

    public function badge(): string
    {
        return match ($this) {
            self::SinGestion => 'secondary',
            self::Iniciado => 'info',
            self::EnInstituto => 'primary',
            self::Retirado => 'success',
        };
    }
}
