<?php

namespace App\Support;

use Illuminate\Support\ViewErrorBag;

/**
 * Cálculos comunes de los componentes x-form.*: id del campo, clave del error
 * (notación con puntos) y aria-describedby (ayuda y/o error).
 */
final class CampoFormulario
{
    public static function id(string $nombre, ?string $id = null): string
    {
        return $id ?? str_replace(['[', ']'], ['_', ''], $nombre);
    }

    public static function claveError(string $nombre): string
    {
        return trim(str_replace(['[', ']'], ['.', ''], $nombre), '.');
    }

    public static function describedBy(string $id, ?string $ayuda, string $claveError, ViewErrorBag $errores): ?string
    {
        $ids = array_filter([
            $ayuda ? "{$id}-ayuda" : null,
            $errores->has($claveError) ? "{$id}-error" : null,
        ]);

        return $ids ? implode(' ', $ids) : null;
    }
}
