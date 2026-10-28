<?php

namespace App\Http\Requests\Academico;

use App\Enums\EstadoGeneral;
use App\Http\Requests\Academico\Concerns\ReglasMateria;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** CU 2: P2 Modificar Materia (nombre y estado). */
class ModificarMateriaRequest extends FormRequest
{
    use ReglasMateria;

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'nombre_materia' => $this->reglaNombre($this->route('materia')->getKey()),
            'estado_materia' => ['required', Rule::enum(EstadoGeneral::class)],
        ];
    }
}
