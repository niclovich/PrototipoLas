<?php

namespace App\Http\Requests\Finanzas\Concerns;

use App\Services\Finanzas\CuotaService;

/**
 * Monto de Cuota (CU 33 alt. 13.1, CU 34 alt. 15.1): mayor a cero, hasta 2 decimales. Acepta "45000", "45000.50",
 * "45.000,50" y "$ 45.000".
 */
trait ReglasMonto
{
    protected function normalizarMonto(string $campo): void
    {
        $valor = $this->input($campo);
        if (is_string($valor)) {
            $this->merge([$campo => static::valorMonto($valor)]);
        }
    }

    /** "$ 45.000,50" → "45000.50". */
    public static function valorMonto(string $valor): string
    {
        $valor = str_replace(['$', ' ', "\u{a0}"], '', trim($valor));
        if (str_contains($valor, ',')) {
            return str_replace(['.', ','], ['', '.'], $valor);
        }

        return preg_match('/^-?\d{1,3}(\.\d{3})+$/', $valor) ? str_replace('.', '', $valor) : $valor;
    }

    /** @return list<mixed> */
    protected function reglasMonto(): array
    {
        return ['bail', 'required', 'regex:/^\d{1,10}(\.\d{1,2})?$/', 'numeric', 'gt:0', 'max:'.CuotaService::MONTO_MAXIMO];
    }
}
