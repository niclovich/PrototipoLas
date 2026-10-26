<?php

namespace App\Contracts;

use App\Models\User;

/**
 * Regla de login de la §1 de la skill: un usuario con rol Alumno solo entra
 * si tiene al menos un Alumno con legajo "Activo".
 */
interface VerificadorLegajoAlumno
{
    public function tieneLegajoActivo(User $usuario): bool;
}
