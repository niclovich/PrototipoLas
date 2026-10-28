<?php

namespace App\Mail;

use App\Models\Alumno;
use App\Models\AlumnoComision;
use App\Services\Alumnos\RegularidadService;

/** CU4 paso 24: notifica al alumno la Condición Final cargada en la materia. */
class CondicionRegistrada extends CorreoAlumno
{
    public function __construct(private readonly AlumnoComision $inscripcion)
    {
        parent::__construct($inscripcion->alumno);
    }

    protected function asunto(): string
    {
        return 'Condición registrada';
    }

    protected function introduccion(): string
    {
        return 'Se registró tu condición en la materia:';
    }

    protected function detalle(Alumno $alumno): array
    {
        $det = $this->inscripcion->detComision;
        $notas = collect(RegularidadService::NOTAS)
            ->map(fn (string $etiqueta, string $campo) => $etiqueta.': '.RegularidadService::formatearNota($this->inscripcion->{$campo}))
            ->implode(' · ');

        return [
            'Materia: '.$det->detallePlan->materia->nombre_materia.' · Comisión: '.$det->comision->codigo_comision,
            $notas.' · Faltas: '.($this->inscripcion->faltas_alumno ?? 0),
            'Condición Final: '.$this->inscripcion->condicion_final?->value,
        ];
    }
}
