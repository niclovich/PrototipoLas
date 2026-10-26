<?php

namespace App\Services\Auth;

use App\Contracts\VerificadorLegajoAlumno;
use App\Models\User;

/**
 * Un usuario Alumno entra solo si tiene al menos un Alumno (legajo por carrera) con estado_legajo "Activo".
 */
class VerificadorLegajoActivo implements VerificadorLegajoAlumno
{
    public function tieneLegajoActivo(User $usuario): bool
    {
        return $usuario->alumnos()->conLegajoActivo()->exists();
    }
}
