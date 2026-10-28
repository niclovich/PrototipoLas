<?php

namespace App\Mail;

use App\Models\Alumno;

/** CU2 paso 26: notifica al alumno su inscripción y el legajo generado. */
class InscripcionRegistrada extends CorreoAlumno
{
    protected function asunto(): string
    {
        return 'Inscripción registrada';
    }

    protected function introduccion(): string
    {
        return 'Tu inscripción quedó registrada y tu legajo está Activo.';
    }

    protected function detalle(Alumno $alumno): array
    {
        return [
            'Número de legajo: '.$alumno->nro_legajo,
            'Carrera: '.$alumno->carrera->nombre_carrera,
            'Estado: '.$alumno->estado_alumno->value,
        ];
    }
}
