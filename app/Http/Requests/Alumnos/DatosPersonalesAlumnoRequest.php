<?php

namespace App\Http\Requests\Alumnos;

use App\Enums\EstadoCivil;
use App\Enums\Sexo;
use App\Http\Requests\Concerns\ReglasDatosUsuario;
use App\Support\DatosUsuario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

/**
 * CU 9 paso 3 (datos personales), pantalla P2 de CU1. Obligatorios según D1 del plan E3: Apellido, Nombre,
 * DNI (lo pone el wizard desde P1, D6), Correo, Provincia y Localidad. El resto es opcional.
 */
class DatosPersonalesAlumnoRequest extends FormRequest
{
    use ReglasDatosUsuario;

    /** Alias de la lista de dominio (App\Support\DatosUsuario::CAMPOS_ALUMNO). */
    public const CAMPOS_USUARIO = DatosUsuario::CAMPOS_ALUMNO;

    public function authorize(): bool
    {
        return (bool) $this->user()?->can('crear_usuario_alumno');
    }

    /**
     * Reglas reutilizables (el servicio o el wizard pueden volver a validar datos guardados en sesión).
     *
     * @param  array<string, mixed>  $datos  necesarios para la Localidad de la Provincia
     * @return array<string, mixed>
     */
    public static function reglas(array $datos): array
    {
        $provinciaLocalidad = static::reglasProvinciaLocalidad($datos['provincia_id'] ?? null);

        return [
            'dni_usuario' => ['required', ...static::reglaDni()],
            'apellido_usuario' => ['required', ...static::reglaApellidoNombre()],
            'nombre_usuario' => ['required', ...static::reglaApellidoNombre()],
            'fecha_nacimiento' => ['nullable', 'date_format:d/m/Y', 'before:today'],
            'sexo' => ['nullable', Rule::enum(Sexo::class)],
            'estado_civil' => ['nullable', Rule::enum(EstadoCivil::class)],
            'email' => ['required', ...static::reglaEmail()],
            'telefono_usuario' => ['nullable', ...static::reglaTelefono()],
            'celular_usuario' => ['nullable', ...static::reglaTelefono()],
            'direccion_usuario' => ['nullable', 'string', 'max:200'],
            'barrio_usuario' => ['nullable', 'string', 'max:100'],
            'provincia_id' => ['required', ...$provinciaLocalidad['provincia_id']],
            'localidad_id' => ['required', ...$provinciaLocalidad['localidad_id']],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:'.config('las.foto_max_kb')],
        ];
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return static::reglas($this->all());
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            ...static::mensajesDatosUsuario(),
            'fecha_nacimiento.date_format' => 'La fecha de nacimiento debe tener el formato dd/mm/aaaa.',
            'fecha_nacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return static::atributosDatosUsuario();
    }

    /**
     * Datos de User ya validados (fecha en Y-m-d), listos para UsuarioAlumnoService::registrar() en el mismo request.
     *
     * @return array<string, mixed>
     */
    public function datosUsuario(): array
    {
        return static::paraUsuario($this->validated());
    }

    /**
     * Contrato para el wizard de CU1 (opción B de D7): en P2 se guarda en la sesión `datosSesion()` (los datos
     * validados tal como llegaron, sin el archivo de foto, que se guarda con UsuarioAlumnoService::guardarFotoTemporal()).
     * En P5 se re-validan con `Validator::make($sesion, self::reglas($sesion))` y recién después se llama a
     * `paraUsuario()` y a `registrar()`.
     *
     * @return array<string, mixed>
     */
    public function datosSesion(): array
    {
        return $this->safe()->except('foto');
    }

    /**
     * @param  array<string, mixed>  $validados  salida de validated()/datosSesion() (fecha dd/mm/aaaa)
     * @return array<string, mixed>
     */
    public static function paraUsuario(array $validados): array
    {
        $datos = array_intersect_key($validados, array_flip(self::CAMPOS_USUARIO));

        if (! empty($datos['fecha_nacimiento'])) {
            $datos['fecha_nacimiento'] = Carbon::createFromFormat('d/m/Y', $datos['fecha_nacimiento'])->toDateString();
        }

        return $datos;
    }
}
