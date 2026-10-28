<?php

namespace App\Http\Requests\Academico\Concerns;

use App\Rules\ResolucionMinisterial;
use App\Services\Academico\CarreraService;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Reglas de P2 Registrar / P2 Modificar Carrera (CU 3/4). Nombre único (sin distinguir mayúsculas: collation
 * de la BD) con espacios normalizados; Resolución con el formato de D1; todos los campos obligatorios (D11).
 */
trait ReglasCarrera
{
    protected function prepareForValidation(): void
    {
        $this->merge(array_map(
            fn ($valor) => is_string($valor) ? Str::squish($valor) : $valor,
            $this->only(['nombre_carrera', 'resolucion_carrera']),
        ));
    }

    /** @return array<string, mixed> */
    protected function reglasDatosCarrera(
        ?int $ignorarId,
        bool $validarFormatoResolucion = true,
        ?int $modalidadActual = null,
        ?int $duracionActual = null,
    ): array {
        return [
            'nombre_carrera' => ['required', 'string', 'max:150', Rule::unique('carreras', 'nombre_carrera')->ignore($ignorarId)],
            'modalidad_id' => ['required', 'integer', Rule::exists('modalidades', 'id')->where(
                fn ($query) => $query->whereNull('deleted_at')
                    ->when($modalidadActual, fn ($actuales, $id) => $actuales->orWhere('id', $id)),
            )],
            'duracion_id' => ['required', 'integer', Rule::exists('duraciones', 'id')->where(
                fn ($query) => $query->whereNull('deleted_at')
                    ->when($duracionActual, fn ($actuales, $id) => $actuales->orWhere('id', $id)),
            )],
            'resolucion_carrera' => ['required', 'string', 'max:50', ...($validarFormatoResolucion ? [new ResolucionMinisterial] : [])],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'nombre_carrera.required' => 'Debe ingresar el nombre de la carrera.',
            'nombre_carrera.unique' => CarreraService::MENSAJE_CARRERA_EXISTENTE,
            'modalidad_id.required' => 'Debe seleccionar una modalidad.',
            'modalidad_id.exists' => 'Debe seleccionar una modalidad.',
            'duracion_id.required' => 'Debe seleccionar una duración.',
            'duracion_id.exists' => 'Debe seleccionar una duración.',
            'resolucion_carrera.required' => 'Debe ingresar la resolución ministerial.',
            'estado_carrera.required' => 'Debe seleccionar un estado.',
            'estado_carrera.enum' => 'Debe seleccionar un estado.',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'nombre_carrera' => 'nombre de la carrera',
            'modalidad_id' => 'modalidad',
            'duracion_id' => 'duración',
            'resolucion_carrera' => 'resolución ministerial',
            'estado_carrera' => 'estado',
        ];
    }
}
