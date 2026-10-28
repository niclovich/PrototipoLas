<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Resolución Ministerial de la Carrera (CU 3 alt. 13, CU 4 alt. 9; decisión D1 de E4):
 * "1234/2020", con prefijo opcional "Resolución N°" (también "Resolucion N"). Se guarda normalizada ("1234/2020").
 */
class ResolucionMinisterial implements ValidationRule
{
    /** Mensaje de la especificación (CU 3 paso 13.1, CU 4 paso 9.1). */
    public const MENSAJE = 'La resolución ministerial no tiene el formato correcto';

    /** Cómo se muestra (P1, P3 y el campo de P2 Modificar). */
    public const PREFIJO = 'Resolución N° ';

    private const PATRON = '/^(?:resoluci[oó]n\s+n[°º.]?\s*)?(\d{1,5}\/\d{4})$/iu';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! preg_match(self::PATRON, trim($value))) {
            $fail(self::MENSAJE);
        }
    }

    /** "Resolución N° 1234/2020" → "1234/2020". Si no tiene el formato, la devuelve sin cambios. */
    public static function normalizar(string $valor): string
    {
        return preg_match(self::PATRON, trim($valor), $partes) ? $partes[1] : trim($valor);
    }

    public static function mostrar(?string $valor): string
    {
        return $valor === null || $valor === '' ? '—' : self::PREFIJO.$valor;
    }
}
