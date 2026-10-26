<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Regla de contraseña del wireframe P2 de Personal: "Mínimo 8 caracteres, una letra mayúscula y un número".
 */
class ContraseniaSegura implements ValidationRule
{
    public const MENSAJE = 'Mínimo 8 caracteres, una letra mayúscula y un número';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || mb_strlen($value) < 8 || ! preg_match('/\p{Lu}/u', $value) || ! preg_match('/\d/', $value)) {
            $fail(self::MENSAJE);
        }
    }
}
