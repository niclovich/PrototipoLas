<?php

namespace App\Http\Requests\Alumnos;

use App\Services\Alumnos\AsistentePreinscripcion;

/**
 * P2 de CU1: las reglas del CU 9 (DatosPersonalesAlumnoRequest). El DNI no viene del input (es de solo
 * lectura) sino del asistente (decisión D6 de E3).
 */
class DatosPersonalesPreinscripcionRequest extends DatosPersonalesAlumnoRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['dni_usuario' => app(AsistentePreinscripcion::class)->dni()]);

        parent::prepareForValidation();
    }
}
