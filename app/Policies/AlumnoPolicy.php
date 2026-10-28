<?php

namespace App\Policies;

use App\Enums\RolSistema;
use App\Models\Alumno;
use App\Models\User;

/**
 * Reglas por registro sobre el Alumno (skill §1: "el alumno solo ve lo suyo"). El permiso de la ruta ya se exigió;
 * un usuario con rol Alumno solo puede operar sobre sus propios legajos (CU5, §7-18).
 */
class AlumnoPolicy
{
    public function gestionarExamenes(User $user, Alumno $alumno): bool
    {
        return ! $user->tieneRol(RolSistema::Alumno) || (int) $alumno->user_id === (int) $user->id;
    }
}
