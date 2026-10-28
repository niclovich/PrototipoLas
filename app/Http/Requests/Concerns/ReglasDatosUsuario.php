<?php

namespace App\Http\Requests\Concerns;

use App\Services\Usuarios\UsuarioService;
use App\Support\DatosUsuario;
use Illuminate\Validation\Rule;

/**
 * Reglas por campo de los datos de Usuario (Hoja 1), sin "required": cada formulario decide qué es obligatorio.
 * Lo usan Personal (CU 25/26) y Usuarios Alumno (CU 9). Los métodos son estáticos para que un servicio pueda
 * volver a validar datos guardados (p. ej. en la sesión del wizard de CU1) sin repetir reglas.
 */
trait ReglasDatosUsuario
{
    /** Correo en minúsculas y DNI solo con dígitos. */
    protected function prepareForValidation(): void
    {
        $this->merge(static::normalizarDatosUsuario($this->all()));
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed> solo las claves presentes, normalizadas
     */
    public static function normalizarDatosUsuario(array $datos): array
    {
        return DatosUsuario::normalizar($datos);
    }

    /** @return list<mixed> */
    public static function reglaDni(?int $ignorarId = null): array
    {
        return ['regex:/^\d{7,8}$/', Rule::unique('users', 'dni_usuario')->ignore($ignorarId)];
    }

    /** @return list<string> */
    public static function reglaApellidoNombre(): array
    {
        return ['string', 'max:100', "regex:/^[\p{L}\s'\-]+$/u"];
    }

    /** Teléfono y celular. @return list<string> */
    public static function reglaTelefono(): array
    {
        return ['string', 'regex:/^[0-9 +()\-]{6,20}$/'];
    }

    /** @return list<mixed> */
    public static function reglaEmail(?int $ignorarId = null): array
    {
        return ['email', 'max:150', Rule::unique('users', 'email')->ignore($ignorarId)];
    }

    /**
     * La Provincia solo filtra la Localidad (Usuario 1..1 Localidad); ninguna dada de baja.
     *
     * @return array<string, list<mixed>>
     */
    public static function reglasProvinciaLocalidad(mixed $provinciaId): array
    {
        return [
            'provincia_id' => ['integer', Rule::exists('provincias', 'id')->whereNull('deleted_at')],
            'localidad_id' => ['integer', Rule::exists('localidades', 'id')->whereNull('deleted_at')->where('provincia_id', (int) $provinciaId)],
        ];
    }

    /** @return array<string, string> */
    public static function mensajesDatosUsuario(): array
    {
        $soloLetras = 'solo puede contener letras, espacios, apóstrofos y guiones.';
        $telefono = 'debe tener entre 6 y 20 caracteres: números, espacios, +, - y paréntesis.';

        return [
            'dni_usuario.unique' => UsuarioService::MENSAJE_DNI_REGISTRADO,
            'dni_usuario.regex' => 'El DNI debe tener 7 u 8 dígitos.',
            'email.unique' => UsuarioService::MENSAJE_CORREO_REGISTRADO,
            'apellido_usuario.regex' => "El apellido {$soloLetras}",
            'nombre_usuario.regex' => "El nombre {$soloLetras}",
            'telefono_usuario.regex' => "El teléfono {$telefono}",
            'celular_usuario.regex' => "El celular {$telefono}",
            'localidad_id.exists' => 'Seleccione una localidad de la provincia elegida.',
        ];
    }

    /** @return array<string, string> */
    public static function atributosDatosUsuario(): array
    {
        return [
            'dni_usuario' => 'DNI',
            'apellido_usuario' => 'apellido',
            'nombre_usuario' => 'nombre',
            'fecha_nacimiento' => 'fecha de nacimiento',
            'sexo' => 'sexo',
            'estado_civil' => 'estado civil',
            'telefono_usuario' => 'teléfono',
            'celular_usuario' => 'celular',
            'email' => 'correo',
            'direccion_usuario' => 'dirección',
            'barrio_usuario' => 'barrio',
            'provincia_id' => 'provincia',
            'localidad_id' => 'localidad',
            'foto' => 'foto',
        ];
    }
}
