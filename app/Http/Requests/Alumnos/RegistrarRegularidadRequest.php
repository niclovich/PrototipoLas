<?php

namespace App\Http\Requests\Alumnos;

use App\Enums\CondicionCursada;
use App\Services\Alumnos\RegularidadService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * CU4 pasos 15–19: notas de parciales y recuperatorios (1 a 10 con hasta 2 decimales, §7-19; alt. 17.1),
 * faltas y Condición Final elegida por el actor (Regular / Libre).
 */
class RegistrarRegularidadRequest extends FormRequest
{
    public const MENSAJE_FALTAS = 'Las faltas deben ser un número entero mayor o igual a cero.';

    public const MENSAJE_CONDICION = 'Seleccione la Condición Final.';

    public function authorize(): bool
    {
        return (bool) $this->user()?->can('crear_regularidad');
    }

    protected function prepareForValidation(): void
    {
        $this->merge(collect(RegularidadService::NOTAS)
            ->mapWithKeys(fn ($etiqueta, string $campo) => [$campo => $this->filled($campo) ? str_replace(',', '.', trim((string) $this->input($campo))) : null])
            ->all());
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $nota = ['nullable', 'regex:/^\d{1,2}(\.\d{1,2})?$/', 'numeric', 'between:1,10'];

        return [
            ...array_fill_keys(array_keys(RegularidadService::NOTAS), $nota),
            'faltas_alumno' => ['nullable', 'integer', 'min:0', 'max:999'],
            'condicion_final' => ['required', Rule::enum(CondicionCursada::class)],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        $mensajes = [];
        foreach (array_keys(RegularidadService::NOTAS) as $campo) {
            foreach (['regex', 'numeric', 'between'] as $regla) {
                $mensajes["{$campo}.{$regla}"] = RegularidadService::MENSAJE_FORMATO_NOTA;
            }
        }

        return [
            ...$mensajes,
            'faltas_alumno.integer' => self::MENSAJE_FALTAS,
            'faltas_alumno.min' => self::MENSAJE_FALTAS,
            'faltas_alumno.max' => self::MENSAJE_FALTAS,
            'condicion_final.required' => self::MENSAJE_CONDICION,
            'condicion_final.enum' => self::MENSAJE_CONDICION,
        ];
    }

    /** @return array{nota_p1: ?string, nota_p2: ?string, nota_r1: ?string, nota_r2: ?string, faltas_alumno: ?int, condicion_final: string} */
    public function datos(): array
    {
        $datos = $this->validated();
        $datos['faltas_alumno'] = isset($datos['faltas_alumno']) ? (int) $datos['faltas_alumno'] : null;

        return $datos;
    }
}
