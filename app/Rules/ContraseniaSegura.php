<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Regla de contraseña del CU 25 (alt. 15.1): mínimo 8 caracteres, una mayúscula y un número.
 * La usan Personal, restablecer y definir contraseña (decisión D15 del plan E2).
 */
class ContraseniaSegura implements ValidationRule
{
    /** Mensaje de la especificación del CU 25, paso 15.1. */
    public const MENSAJE = 'La contraseña debe tener al menos 8 caracteres, una letra mayúscula y un número';

    /** Texto de ayuda junto al campo (wireframe Registrar P2). */
    public const AYUDA = 'Mínimo 8 caracteres, una letra mayúscula y un número.';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || mb_strlen($value) < 8 || ! preg_match('/\p{Lu}/u', $value) || ! preg_match('/\d/', $value)) {
            $fail(self::MENSAJE);
        }
    }
}
