<?php

namespace App\Http\Requests\Academico;

use App\Enums\EstadoGeneral;
use App\Models\PlanEstudio;
use App\Services\Academico\DetallePlanEstudioService as Svc;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * CU 6 pasos 6–23. Materia y código son únicos en el plan en cualquier estado (E7-D1); Año de Dictado dentro de la
 * duración de la carrera; carga horaria entera entre 1 y 999 (§7-19).
 */
class RegistrarMateriaPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['codigo_det_plan_estudio' => Str::squish((string) $this->input('codigo_det_plan_estudio'))]);
    }

    public function rules(): array
    {
        /** @var PlanEstudio $plan */
        $plan = $this->route('plan');
        $duracion = max(1, (int) $plan->carrera->duracion?->cantidad_anios);

        return [
            'materia_id' => ['bail', 'required', 'integer',
                Rule::exists('materias', 'id')->where('estado_materia', EstadoGeneral::Activo->value),
                Rule::unique('detalles_plan_estudio', 'materia_id')->where('plan_estudio_id', $plan->id)],
            'codigo_det_plan_estudio' => ['bail', 'required', 'string', 'max:20',
                Rule::unique('detalles_plan_estudio', 'codigo_det_plan_estudio')->where('plan_estudio_id', $plan->id)],
            'regimen_id' => ['required', 'integer', Rule::exists('regimenes', 'id')->whereNull('deleted_at')],
            'cuatrimestre_id' => ['required', 'integer', Rule::exists('cuatrimestres', 'id')->whereNull('deleted_at')],
            'anio_curso_det_plan_estudio' => ['required', 'integer', 'between:1,'.$duracion],
            'carga_horaria_det_plan_estudio' => ['bail', 'required', 'integer', 'between:1,'.Svc::CARGA_MAXIMA],
        ];
    }

    public function messages(): array
    {
        return [
            'materia_id.unique' => Svc::MENSAJE_MATERIA_YA_CARGADA,
            'materia_id.*' => 'Debe seleccionar una materia activa.',
            'codigo_det_plan_estudio.unique' => Svc::MENSAJE_CODIGO_EXISTENTE,
            'codigo_det_plan_estudio.required' => 'Debe ingresar el código de la materia.',
            'codigo_det_plan_estudio.max' => 'El código no puede superar los 20 caracteres.',
            'regimen_id.*' => 'Debe seleccionar un régimen.',
            'cuatrimestre_id.*' => 'Debe seleccionar un cuatrimestre.',
            'anio_curso_det_plan_estudio.*' => 'Debe seleccionar un año de dictado válido.',
            'carga_horaria_det_plan_estudio.*' => Svc::MENSAJE_CARGA_INVALIDA,
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            if ($validator->errors()->hasAny(['regimen_id', 'cuatrimestre_id'])) {
                return;
            }
            if (! app(Svc::class)->cuatrimestreCorrespondeAlRegimen((int) $this->input('regimen_id'), (int) $this->input('cuatrimestre_id'))) {
                $validator->errors()->add('cuatrimestre_id', Svc::MENSAJE_CUATRIMESTRE_REGIMEN);
            }
        }];
    }
}
