<?php

namespace App\Http\Requests\Academico;

use App\Models\Carrera;
use App\Services\Academico\ComisionService as Svc;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * CU 17 pasos 4–21 (P2 Registrar Comisión). Código único por carrera, año de curso y año lectivo (alt. 16.1);
 * Año de Curso dentro de la duración de la carrera (alt. 10.1); Año Lectivo desde el actual (alt. 13.1, E9-D4);
 * Capacidad entera mayor a cero (alt. 19.1).
 */
class RegistrarComisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(array_map(
            fn ($valor) => is_string($valor) ? Str::squish($valor) : $valor,
            $this->only(['codigo_comision', 'horario_comision']),
        ));
    }

    public function rules(): array
    {
        /** @var Carrera $carrera */
        $carrera = $this->route('carrera');
        $actual = (int) now()->format('Y');

        return [
            'codigo_comision' => ['bail', 'required', 'string', 'max:20',
                Rule::unique('comisiones', 'codigo_comision')
                    ->where('carrera_id', $carrera->id)
                    ->where('anio_curso', (int) $this->input('anio_curso'))
                    ->where('anio_lectivo', (int) $this->input('anio_lectivo'))],
            'modalidad_id' => ['required', 'integer', Rule::exists('modalidades', 'id')->whereNull('deleted_at')],
            'capacidad_comision' => ['bail', 'required', 'integer', 'min:1', 'max:65535'],
            'anio_curso' => ['bail', 'required', 'integer', 'between:1,'.app(Svc::class)->duracion($carrera)],
            'anio_lectivo' => ['bail', 'required', 'integer', 'min:'.$actual, 'max:'.($actual + 1)],
            'horario_comision' => ['required', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'codigo_comision.unique' => Svc::MENSAJE_CODIGO_EXISTENTE,
            'codigo_comision.required' => 'Debe ingresar el código de la comisión.',
            'codigo_comision.max' => 'El código no puede superar los 20 caracteres.',
            'modalidad_id.*' => 'Debe seleccionar una modalidad.',
            'capacidad_comision.*' => Svc::MENSAJE_CAPACIDAD,
            'anio_curso.*' => Svc::MENSAJE_ANIO_CURSO,
            'anio_lectivo.min' => Svc::MENSAJE_ANIO_LECTIVO,
            'anio_lectivo.max' => 'El Año Lectivo no puede ser posterior al año próximo.',
            'anio_lectivo.*' => 'Debe ingresar un Año Lectivo válido.',
            'horario_comision.required' => 'Debe ingresar el horario de la comisión.',
            'horario_comision.max' => 'El horario no puede superar los 100 caracteres.',
        ];
    }
}
