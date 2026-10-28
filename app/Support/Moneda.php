<?php

namespace App\Support;

/** Montos en formato argentino: $45.000 o $45.000,50 (wireframe de Gestión de Cuotas). */
final class Moneda
{
    public static function formatear(float|int|string|null $monto): string
    {
        $valor = (float) $monto;
        $decimales = fmod($valor, 1.0) === 0.0 ? 0 : 2;

        return '$'.number_format($valor, $decimales, ',', '.');
    }
}
