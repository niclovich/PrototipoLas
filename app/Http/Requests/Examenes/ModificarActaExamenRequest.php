<?php

namespace App\Http\Requests\Examenes;

use App\Http\Requests\Examenes\Concerns\ReglasActaExamen;
use Illuminate\Foundation\Http\FormRequest;

/** CU 22 pasos 10–27 (P2 Modificar Acta de Examen): la carrera y la materia no cambian. */
class ModificarActaExamenRequest extends FormRequest
{
    use ReglasActaExamen;

    public function rules(): array
    {
        return $this->reglasActa();
    }
}
