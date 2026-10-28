<?php

namespace App\Http\Requests\Personal\Concerns;

use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Http\Requests\Concerns\ReglasDatosUsuario;
use Illuminate\Validation\Rule;

/**
 * Reglas de P2 Registrar y P2 Modificar Personal (CU 25/26). Todos los campos del wireframe son obligatorios;
 * los formatos son los de ReglasDatosUsuario (decisión D9 del plan E2).
 */
trait ReglasDatosPersonal
{
    use ReglasDatosUsuario;

    /** @return array<string, mixed> */
    protected function reglasDatosPersonales(?int $ignorarId): array
    {
        $provinciaLocalidad = static::reglasProvinciaLocalidad($this->input('provincia_id'));

        return [
            'apellido_usuario' => ['required', ...static::reglaApellidoNombre()],
            'nombre_usuario' => ['required', ...static::reglaApellidoNombre()],
            'telefono_usuario' => ['required', ...static::reglaTelefono()],
            'email' => ['required', ...static::reglaEmail($ignorarId)],
            'direccion_usuario' => ['required', 'string', 'max:200'],
            'provincia_id' => ['required', ...$provinciaLocalidad['provincia_id']],
            'localidad_id' => ['required', ...$provinciaLocalidad['localidad_id']],
            'rol_id' => [
                'required', 'integer',
                Rule::exists('roles', 'id')->where('estado', EstadoGeneral::Activo->value)->whereIn('name', RolSistema::valoresDelPersonal()),
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            ...static::mensajesDatosUsuario(),
            'rol_id.exists' => 'Seleccione un rol del personal.',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [...static::atributosDatosUsuario(), 'rol_id' => 'rol', 'password' => 'contraseña'];
    }

    /** @return array<string, mixed> atributos de User (sin provincia ni rol). */
    public function datosUsuario(): array
    {
        return $this->safe()->except(['provincia_id', 'rol_id', 'password']);
    }
}
