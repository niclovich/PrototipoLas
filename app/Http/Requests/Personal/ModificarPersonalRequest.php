<?php

namespace App\Http\Requests\Personal;

use App\Http\Requests\Personal\Concerns\ReglasDatosPersonal;
use Illuminate\Foundation\Http\FormRequest;

/** CU 26: P2 Modificar Personal. El DNI no se modifica; cualquier error abre "Datos inválidos" (alt. 14). */
class ModificarPersonalRequest extends FormRequest
{
    use ReglasDatosPersonal;

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return $this->reglasDatosPersonales($this->route('personal')->getKey());
    }

    /** @return array<string, mixed> */
    public function datosUsuario(): array
    {
        return $this->safe()->except(['provincia_id', 'rol_id', 'password', 'dni_usuario']);
    }
}
