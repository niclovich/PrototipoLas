<?php

namespace App\Http\Requests\Alumnos;

use App\Enums\SituacionEstudios;
use App\Services\Alumnos\PreinscripcionService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** CU1 pasos 10–21 (P4 Carrera y documentacion). Los documentos son opcionales (alt. 18); Observaciones opcional (E12-D1). */
class CarreraPreinscripcionRequest extends FormRequest
{
    public const MENSAJE_PDF = 'El archivo debe estar en formato PDF.';

    public function authorize(): bool
    {
        return (bool) $this->user()?->can('crear_preinscripcion');
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['adeuda_materia' => $this->boolean('adeuda_materia')]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $carreras = app(PreinscripcionService::class)->carrerasDisponibles()->modelKeys();
        $pdf = ['nullable', 'file', 'mimes:pdf', 'max:'.config('las.documento_max_kb')];

        return [
            'carrera_id' => ['required', 'integer', Rule::in($carreras)],
            'situacion_estudios' => ['required', Rule::enum(SituacionEstudios::class)],
            'adeuda_materia' => ['boolean'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
            ...array_fill_keys(array_keys(PreinscripcionService::DOCUMENTOS), $pdf),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'carrera_id.required' => 'Seleccione una carrera.',
            'carrera_id.in' => 'Seleccione una carrera de la lista.',
            'situacion_estudios.required' => 'Seleccione la situación de estudios secundarios.',
            'situacion_estudios.enum' => 'Seleccione la situación de estudios secundarios.',
            'documento_dni.mimes' => self::MENSAJE_PDF,
            'documento_titulo.mimes' => self::MENSAJE_PDF,
            'documento_dni.file' => self::MENSAJE_PDF,
            'documento_titulo.file' => self::MENSAJE_PDF,
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['documento_dni' => 'fotocopia de DNI', 'documento_titulo' => 'certificado de título', 'observaciones' => 'observaciones'];
    }

    /** @return array{carrera_id: int, situacion_estudios: string, adeuda_materia: bool, observaciones: ?string} */
    public function datosCarrera(): array
    {
        return [
            'carrera_id' => (int) $this->validated('carrera_id'),
            'situacion_estudios' => (string) $this->validated('situacion_estudios'),
            'adeuda_materia' => (bool) $this->validated('adeuda_materia'),
            'observaciones' => $this->validated('observaciones') ?: null,
        ];
    }
}
