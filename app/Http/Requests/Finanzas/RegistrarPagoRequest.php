<?php

namespace App\Http\Requests\Finanzas;

use App\Services\Finanzas\PagoCuotasService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** CU7 pasos 8, 15 y 18: cuotas marcadas con "Cobrar", medio de pago agregado e importe. */
class RegistrarPagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('crear_pago');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'cuotas' => ['required', 'array', 'min:1'],
            'cuotas.*' => ['integer', 'distinct'],
            'medio_pago_id' => ['required', 'integer', Rule::exists('medios_pago', 'id')->whereNull('deleted_at')],
            'importe' => ['required', 'numeric', 'gt:0'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'cuotas.required' => PagoCuotasService::MENSAJE_CUOTAS,
            'cuotas.min' => PagoCuotasService::MENSAJE_CUOTAS,
            'medio_pago_id.required' => 'Seleccione la forma de pago y presione "Agregar".',
            'medio_pago_id.exists' => 'Seleccione una forma de pago de la lista.',
            'importe.required' => PagoCuotasService::MENSAJE_IMPORTE,
            'importe.gt' => PagoCuotasService::MENSAJE_IMPORTE,
            'importe.numeric' => PagoCuotasService::MENSAJE_IMPORTE,
        ];
    }
}
