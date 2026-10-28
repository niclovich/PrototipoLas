<?php

namespace App\Http\Requests\Examenes;

use App\Services\Examenes\AsentamientoActaService;
use Illuminate\Foundation\Http\FormRequest;

/** CU6 pasos 9–13: nota de 1 a 10 con hasta 2 decimales (alt. 10.1, §7-19) y observación opcional. */
class RegistrarNotaExamenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('crear_asentamiento_acta');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nota_acta' => $this->filled('nota_acta') ? str_replace(',', '.', trim((string) $this->input('nota_acta'))) : null,
            'observacion' => $this->filled('observacion') ? trim((string) $this->input('observacion')) : null,
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'nota_acta' => ['required', 'regex:/^\d{1,2}(\.\d{1,2})?$/', 'numeric', 'between:1,10'],
            'observacion' => ['nullable', 'string', 'max:255'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            ...array_fill_keys(['nota_acta.required', 'nota_acta.regex', 'nota_acta.numeric', 'nota_acta.between'], AsentamientoActaService::MENSAJE_NOTA),
            'observacion.max' => 'La observación no puede superar los 255 caracteres.',
        ];
    }
}
