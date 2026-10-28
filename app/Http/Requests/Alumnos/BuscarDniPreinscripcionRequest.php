<?php

namespace App\Http\Requests\Alumnos;

use App\Support\DatosUsuario;
use Illuminate\Foundation\Http\FormRequest;

/** CU1 pasos 2–3: "DNI del alumno" en P1. */
class BuscarDniPreinscripcionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('crear_preinscripcion');
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['dni' => DatosUsuario::normalizarDni($this->input('dni'))]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['dni' => ['required', 'regex:/^\d{7,8}$/']];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'dni.required' => 'Ingrese el DNI del alumno.',
            'dni.regex' => 'El DNI debe tener 7 u 8 dígitos.',
        ];
    }
}
