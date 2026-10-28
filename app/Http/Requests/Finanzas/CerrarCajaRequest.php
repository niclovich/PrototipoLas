<?php

namespace App\Http\Requests\Finanzas;

use App\Http\Requests\Finanzas\Concerns\ReglasMonto;
use App\Services\Finanzas\CajaService;
use Illuminate\Foundation\Http\FormRequest;

/** CU 36 pasos 7–8 y 13: Monto Contado por Medio de Pago (≥ 0) y Observación. La regla del paso 14 la aplica CajaService. */
class CerrarCajaRequest extends FormRequest
{
    use ReglasMonto;

    protected function prepareForValidation(): void
    {
        $contados = $this->input('contados');
        if (is_array($contados)) {
            $this->merge(['contados' => array_map(fn ($v) => is_string($v) ? static::valorMonto($v) : $v, $contados)]);
        }
    }

    public function authorize(): bool
    {
        return (bool) $this->user()?->can('editar_caja');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'contados' => ['required', 'array'],
            'contados.*' => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'observacion_cierre' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'contados.required' => CajaService::MENSAJE_MONTO_CONTADO,
            'contados.*.required' => CajaService::MENSAJE_MONTO_CONTADO,
            'contados.*.numeric' => CajaService::MENSAJE_MONTO_CONTADO,
            'contados.*.min' => CajaService::MENSAJE_MONTO_CONTADO,
        ];
    }
}
