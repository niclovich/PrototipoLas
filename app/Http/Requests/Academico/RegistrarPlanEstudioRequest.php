<?php

namespace App\Http\Requests\Academico;

use App\Services\Academico\PlanEstudioService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** CU 5 pasos 6–7: año del Plan de Estudio (E6-D8: entero entre 1990 y el año próximo, sin repetir en la carrera). */
class RegistrarPlanEstudioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'anio_plan_estudio' => [
                'bail', 'required', 'integer', 'between:'.PlanEstudioService::ANIO_MINIMO.','.(now()->year + 1),
                Rule::unique('planes_estudio', 'anio_plan_estudio')->where('carrera_id', $this->route('carrera')->getKey()),
            ],
        ];
    }

    public function messages(): array
    {
        return ['anio_plan_estudio.*' => PlanEstudioService::MENSAJE_ANIO_INVALIDO];
    }
}
