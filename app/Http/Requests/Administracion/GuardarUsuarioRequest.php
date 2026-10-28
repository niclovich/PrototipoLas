<?php

namespace App\Http\Requests\Administracion;

use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Http\Requests\Concerns\ReglasDatosUsuario;
use App\Models\Rol;
use App\Services\Usuarios\UsuarioService;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Alta y modificación de usuarios desde Administración (permiso por ruta).
 */
class GuardarUsuarioRequest extends FormRequest
{
    use ReglasDatosUsuario;

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $id = $this->route('usuario')?->getKey();

        return [
            'dni_usuario' => ['required', ...static::reglaDni($id)],
            'apellido_usuario' => ['required', 'string', 'max:100'],
            'nombre_usuario' => ['required', 'string', 'max:100'],
            'email' => ['required', ...static::reglaEmail($id)],
            'rol' => [
                'required',
                Rule::notIn([RolSistema::Alumno->value]),
                Rule::exists(Rol::class, 'name')->where('estado', EstadoGeneral::Activo->value),
                function (string $atributo, mixed $valor, Closure $fallar) {
                    if ($valor === RolSistema::SuperAdmin->value && ! $this->user()->tieneRol(RolSistema::SuperAdmin)) {
                        $fallar(UsuarioService::MENSAJE_SOLO_SUPERADMIN);
                    }
                },
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            ...static::mensajesDatosUsuario(),
            'rol.not_in' => 'Los usuarios Alumno se registran desde la Preinscripción.',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'dni_usuario' => 'DNI',
            'apellido_usuario' => 'apellido',
            'nombre_usuario' => 'nombre',
            'email' => 'correo electrónico',
            'rol' => 'rol',
        ];
    }

    /** @return array{dni_usuario: string, apellido_usuario: string, nombre_usuario: string, email: string} */
    public function datosUsuario(): array
    {
        return $this->safe()->except('rol');
    }
}
