<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Regla de negocio que termina el caso de uso con un aviso (p. ej. CU 2 alt. 4: materia vinculada a un
 * Plan de Estudio). El mensaje es el texto exacto de la especificación; el controller lo muestra en P1.
 * lista: renglones que acompañan al mensaje (p. ej. CU3 alt. 5.2: cuotas pendientes).
 */
class OperacionNoPermitida extends RuntimeException
{
    /** @param  list<string>  $lista */
    public function __construct(string $message = '', public readonly array $lista = [])
    {
        parent::__construct($message);
    }
}
