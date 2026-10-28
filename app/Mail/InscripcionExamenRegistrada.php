<?php

namespace App\Mail;

use App\Models\ActaExamen;
use App\Models\Alumno;

/** CU5 paso 17: notifica al alumno su inscripción al acta de examen. */
class InscripcionExamenRegistrada extends CorreoAlumno
{
    public function __construct(Alumno $alumno, private readonly ActaExamen $acta)
    {
        parent::__construct($alumno);
    }

    protected function asunto(): string
    {
        return 'Inscripción a examen';
    }

    protected function introduccion(): string
    {
        return 'Quedaste inscripto en el acta de examen. Estos son sus datos:';
    }

    protected function detalle(Alumno $alumno): array
    {
        $this->acta->loadMissing(['detallePlan.materia', 'llamado', 'modalidad']);

        return [
            'Materia: '.$this->acta->detallePlan->materia->nombre_materia,
            'Nro Acta: '.$this->acta->nro_acta_texto.' · Llamado: '.$this->acta->llamado->llamado,
            'Fecha de examen: '.$this->acta->fecha_acta->format('d/m/Y').' · Modalidad: '.$this->acta->modalidad->modalidad,
            'Carrera: '.$alumno->carrera->nombre_carrera,
        ];
    }
}
