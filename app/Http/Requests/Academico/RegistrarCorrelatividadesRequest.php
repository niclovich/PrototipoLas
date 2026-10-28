<?php

namespace App\Http\Requests\Academico;

use App\Enums\CondicionCorrelativa;
use App\Models\DetallePlanEstudio;
use App\Services\Academico\CorrelatividadService as Svc;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** CU 8 pasos 9–19: cada fila de la tabla "CORRELATIVAS DE LA MATERIA" (materia de año anterior + condición R/A). */
class RegistrarCorrelatividadesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var DetallePlanEstudio $detalle */
        $detalle = $this->route('detalle');
        $disponibles = app(Svc::class)->disponibles($detalle)->modelKeys();

        return [
            'correlativas' => ['nullable', 'array'],
            'correlativas.*.detalle_plan_correlativa_id' => ['bail', 'required', 'integer', 'distinct', Rule::in($disponibles)],
            'correlativas.*.p_cursar' => ['required', Rule::enum(CondicionCorrelativa::class)],
            'correlativas.*.p_rendir' => ['required', Rule::enum(CondicionCorrelativa::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'correlativas.*.detalle_plan_correlativa_id.distinct' => Svc::MENSAJE_CORRELATIVA_REPETIDA,
            'correlativas.*.detalle_plan_correlativa_id.*' => Svc::MENSAJE_CORRELATIVA_INVALIDA,
            'correlativas.*.p_cursar.*' => Svc::MENSAJE_CONDICION,
            'correlativas.*.p_rendir.*' => Svc::MENSAJE_CONDICION,
        ];
    }

    /** @return list<array{detalle_plan_correlativa_id: int|string, p_cursar: string, p_rendir: string}> */
    public function filas(): array
    {
        return array_values($this->validated('correlativas') ?? []);
    }
}
