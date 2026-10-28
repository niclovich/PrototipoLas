<?php

namespace App\Http\Requests\Personal;

use App\Models\Motivo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** CU 27: modal Registrar Baja de Personal. Motivo obligatorio, observación opcional (D17). */
class RegistrarBajaPersonalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'motivo_id' => [
                'required', 'integer',
                Rule::exists('motivos', 'id')->where('accion', Motivo::ACCION_BAJA_PERSONAL)->whereNull('deleted_at'),
            ],
            'observacion_novedad' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['motivo_id.required' => 'Seleccione un motivo de baja.'];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['motivo_id' => 'motivo de baja', 'observacion_novedad' => 'observación'];
    }
}
