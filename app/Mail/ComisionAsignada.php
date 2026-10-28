<?php

namespace App\Mail;

use App\Models\Alumno;
use App\Models\Comision;

/** CU3 paso 20 y CU 19 paso 16: notifica al alumno el Código y Horario de la comisión (nueva o de destino). */
class ComisionAsignada extends CorreoAlumno
{
    public function __construct(Alumno $alumno, private readonly Comision $comision, private readonly bool $cambio = false)
    {
        parent::__construct($alumno);
    }

    protected function asunto(): string
    {
        return $this->cambio ? 'Cambio de comisión' : 'Inscripción en comisión';
    }

    protected function introduccion(): string
    {
        return $this->cambio
            ? 'Se registró tu cambio de comisión. Estos son los datos de tu nueva comisión:'
            : 'Quedaste inscripto en la comisión. Estos son sus datos:';
    }

    protected function detalle(Alumno $alumno): array
    {
        return [
            'Comisión: '.$this->comision->codigo_comision,
            'Horario: '.$this->comision->horario_comision,
            'Año de curso: '.$this->comision->anio_curso.' · Año lectivo: '.$this->comision->anio_lectivo,
            'Carrera: '.$alumno->carrera->nombre_carrera,
        ];
    }
}
