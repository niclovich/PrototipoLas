<?php

namespace App\Services\Numeracion;

use Illuminate\Support\Facades\DB;
use LogicException;

/**
 * Números correlativos globales (decisión §7-25): nro de acta, legajo, nro de comprobante.
 * Debe llamarse dentro de una transacción: bloquea las filas leídas hasta el commit; la columna tiene UNIQUE.
 */
class NumeradorService
{
    public function siguiente(string $tabla, string $columna, int $inicial = 1): int
    {
        if (DB::transactionLevel() === 0) {
            throw new LogicException('NumeradorService::siguiente() requiere una transacción.');
        }

        $maximo = DB::table($tabla)->lockForUpdate()->max($columna);

        return $maximo === null ? $inicial : max($inicial, (int) $maximo + 1);
    }
}
