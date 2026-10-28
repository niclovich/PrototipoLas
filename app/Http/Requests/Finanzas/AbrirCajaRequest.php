<?php

namespace App\Http\Requests\Finanzas;

use App\Http\Requests\Finanzas\Concerns\ReglasMonto;
use App\Services\Finanzas\CajaService;
use Illuminate\Foundation\Http\FormRequest;

/** CU 35 pasos 4–5: Monto Inicial (mayor a cero). */
class AbrirCajaRequest extends FormRequest
{
    use ReglasMonto;

    protected function prepareForValidation(): void
    {
        $this->normalizarMonto('monto_inicial');
    }

    public function authorize(): bool
    {
        return (bool) $this->user()?->can('crear_caja');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['monto_inicial' => ['required', 'numeric', 'gt:0', 'max:9999999999']];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'monto_inicial.required' => CajaService::MENSAJE_MONTO_INICIAL,
            'monto_inicial.numeric' => CajaService::MENSAJE_MONTO_INICIAL,
            'monto_inicial.gt' => CajaService::MENSAJE_MONTO_INICIAL,
        ];
    }
}
