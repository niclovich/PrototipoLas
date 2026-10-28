<?php

namespace App\Http\Requests\Finanzas;

use App\Http\Requests\Finanzas\Concerns\ReglasMonto;
use App\Services\Finanzas\CuotaService as Svc;
use Illuminate\Foundation\Http\FormRequest;

/** CU 33 pasos 4–13 (P2 Registrar Cuota). La unicidad Carrera + año + Mes entre cuotas "Activo" la valida el Service. */
class RegistrarCuotaRequest extends FormRequest
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
        $actual = Svc::anioActual();

        return [
            'mes_id' => ['required', 'integer', 'exists:meses,id'],
            'anio' => ['bail', 'required', 'integer', 'min:'.$actual, 'max:'.($actual + 1)],
            'monto' => $this->reglasMonto(),
        ];
    }

    public function messages(): array
    {
        return [
            'mes_id.*' => 'Debe seleccionar un mes.',
            'anio.min' => Svc::MENSAJE_ANIO_MENOR,
            'anio.max' => 'El año lectivo no puede ser posterior al año próximo.',
            'anio.*' => 'Debe ingresar un año lectivo válido.',
            'monto.*' => Svc::MENSAJE_MONTO_INVALIDO,
        ];
    }
}
