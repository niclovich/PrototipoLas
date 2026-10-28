<?php

namespace App\Http\Requests\Finanzas;

use App\Http\Requests\Finanzas\Concerns\ReglasMonto;
use App\Services\Finanzas\CuotaService as Svc;
use Illuminate\Foundation\Http\FormRequest;

/** CU 34 pasos 14–15 (P2 Modificar Cuota): solo el nuevo monto. */
class ModificarCuotaRequest extends FormRequest
{
    use ReglasMonto;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizarMonto('monto');
    }

    public function rules(): array
    {
        return ['monto' => $this->reglasMonto()];
    }

    public function messages(): array
    {
        return ['monto.*' => Svc::MENSAJE_MONTO_INVALIDO];
    }
}
