<?php

namespace App\Http\Requests\Examenes;

use App\Services\Examenes\AsentamientoActaService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** CU6 pasos 15–16: número de folio entero positivo no usado por otra acta (alt. 16.1, §7-19). */
class AsentarActaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('crear_asentamiento_acta');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['nro_folio' => ['required', 'integer', 'min:1', 'max:999999', Rule::unique('actas_examen', 'nro_folio')]];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return array_fill_keys(
            ['nro_folio.required', 'nro_folio.integer', 'nro_folio.min', 'nro_folio.max', 'nro_folio.unique'],
            AsentamientoActaService::MENSAJE_FOLIO,
        );
    }
}
