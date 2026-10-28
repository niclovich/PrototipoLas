<?php

namespace App\Http\Requests\Personal;

use App\Http\Requests\Personal\Concerns\ReglasDatosPersonal;
use App\Rules\ContraseniaSegura;
use Illuminate\Foundation\Http\FormRequest;

/** CU 25: P2 Registrar Personal (DNI único: alt. 4; contraseña: alt. 15). */
class RegistrarPersonalRequest extends FormRequest
{
    use ReglasDatosPersonal {
        messages as mensajesDatosPersonales;
    }

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'dni_usuario' => ['required', ...static::reglaDni()],
            ...$this->reglasDatosPersonales(null),
            'password' => ['required', 'string', new ContraseniaSegura],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            ...$this->mensajesDatosPersonales(),
            'password.required' => ContraseniaSegura::MENSAJE,
        ];
    }
}
