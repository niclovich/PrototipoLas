<?php

namespace App\Http\Requests\Academico;

use App\Http\Requests\Academico\Concerns\ReglasMateria;
use Illuminate\Foundation\Http\FormRequest;

/** CU 1: P2 Registrar Materia. El Estado no viaja (combo deshabilitado): el servidor registra "Activo". */
class RegistrarMateriaRequest extends FormRequest
{
    use ReglasMateria;

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['nombre_materia' => $this->reglaNombre(null)];
    }
}
