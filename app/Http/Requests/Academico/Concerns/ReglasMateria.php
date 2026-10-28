<?php

namespace App\Http\Requests\Academico\Concerns;

use App\Services\Academico\MateriaService;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Reglas de P2 Registrar / P2 Modificar Materia (CU 1/2). Nombre único sin distinguir mayúsculas (collation)
 * y con espacios normalizados.
 */
trait ReglasMateria
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('nombre_materia'))) {
            $this->merge(['nombre_materia' => Str::squish($this->input('nombre_materia'))]);
        }
    }

    /** @return list<mixed> */
    protected function reglaNombre(?int $ignorarId): array
    {
        return ['required', 'string', 'max:150', Rule::unique('materias', 'nombre_materia')->ignore($ignorarId)];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'nombre_materia.required' => 'Debe ingresar el nombre de la materia.',
            'nombre_materia.unique' => MateriaService::MENSAJE_MATERIA_EXISTENTE,
            'estado_materia.required' => 'Debe seleccionar un estado.',
            'estado_materia.enum' => 'Debe seleccionar un estado.',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['nombre_materia' => 'nombre de la materia', 'estado_materia' => 'estado'];
    }
}
