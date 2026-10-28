<?php

namespace App\Http\Requests\Personal;

use Illuminate\Foundation\Http\FormRequest;

/** CU 25 paso 4: verificación del DNI al ingresarlo (AJAX). */
class VerificarDniRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['dni_usuario' => ['required', 'string', 'max:15']];
    }
}
