<?php

namespace App\Mail;

use App\Models\Alumno;
use App\Models\DetalleActa;
use App\Services\Alumnos\RegularidadService;

/** CU6 paso 24: notifica al alumno la nota y la Condición Final del examen. */
class ResultadoExamen extends CorreoAlumno
{
    public function __construct(Alumno $alumno, private readonly DetalleActa $detalle)
    {
        parent::__construct($alumno);
    }

    protected function asunto(): string
    {
        return 'Resultado de examen';
    }

    protected function introduccion(): string
    {
        return 'Se asentó el acta de tu examen. Este es tu resultado:';
    }

    protected function detalle(Alumno $alumno): array
    {
        $acta = $this->detalle->acta()->with(['detallePlan.materia'])->first();

        return [
            'Materia: '.$acta->detallePlan->materia->nombre_materia,
            'Nro Acta: '.$acta->nro_acta_texto.' · Fecha: '.$acta->fecha_acta->format('d/m/Y'),
            'Nota: '.RegularidadService::formatearNota($this->detalle->nota_acta),
            'Condición Final: '.$this->detalle->condicion_final->value,
        ];
    }
}
