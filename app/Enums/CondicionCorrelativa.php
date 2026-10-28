<?php

namespace App\Enums;

use App\Enums\Concerns\EnumConEtiqueta;

/**
 * Correlativa.pCursar / pRendir (CU 8).
 */
enum CondicionCorrelativa: string
{
    use EnumConEtiqueta;

    case Regular = 'Regular';
    case Aprobada = 'Aprobada';

    /** "R" / "A" (P2 y P3 de CU 8). */
    public function inicial(): string
    {
        return mb_substr($this->value, 0, 1);
    }

    /** Opción del combo de P2 de CU 8: "R - Regular". */
    public function opcion(): string
    {
        return "{$this->inicial()} - {$this->value}";
    }

    /** Celda de la tabla de P2 de CU 8: "R (Regular)". */
    public function celda(): string
    {
        return "{$this->inicial()} ({$this->value})";
    }

    public function badge(): string
    {
        return match ($this) {
            self::Regular => 'info',
            self::Aprobada => 'success',
        };
    }
}
