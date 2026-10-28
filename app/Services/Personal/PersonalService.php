<?php

namespace App\Services\Personal;

use App\Models\Motivo;
use App\Models\Novedad;
use App\Models\Rol;
use App\Models\User;
use App\Services\Usuarios\UsuarioService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * CU 25 Registrar, CU 26 Modificar y CU 27 Registrar Baja de Personal (Director General).
 * La lógica de cuenta (alta, actualización, desactivación) es la de UsuarioService: no se duplica.
 */
class PersonalService
{
    /** CU 25, alternativo 4.1. */
    public const MENSAJE_DNI_REGISTRADO = UsuarioService::MENSAJE_DNI_REGISTRADO;

    /** CU 26, alternativo 14.1. */
    public const MENSAJE_DATOS_INVALIDOS = 'Debe completar los datos obligatorios con un formato válido';

    /** Decisión D10 del plan E2 (sin escenario en la spec). */
    public const MENSAJE_CORREO_REGISTRADO = UsuarioService::MENSAJE_CORREO_REGISTRADO;

    /** Textos de las pantallas de Confirmación (decisión D6). */
    public const CONFIRMACION_REGISTRO = 'El Personal se registró correctamente.';

    public const CONFIRMACION_MODIFICACION = 'Los datos del Personal se modificaron correctamente.';

    public const CONFIRMACION_BAJA = 'La baja del Personal se registró correctamente.';

    public function __construct(private readonly UsuarioService $usuarios) {}

    /** Pasos 11–12 (CU 25/26): roles activos del personal con su descripción. */
    public function rolesDelPersonal(): Collection
    {
        return Rol::query()->delPersonal()->orderBy('name')->get(['id', 'name', 'descripcion']);
    }

    /** Pasos 6–8 (CU 27): motivos de baja disponibles. */
    public function motivosDeBaja(): Collection
    {
        return Motivo::query()->deAccion(Motivo::ACCION_BAJA_PERSONAL)->orderBy('descripcion')->get(['id', 'descripcion']);
    }

    /** Paso 4 (CU 25): cualquier usuario (personal, alumno o inactivo) con ese DNI. */
    public function dniRegistrado(string $dni): bool
    {
        return $this->usuarios->buscarPorDni($dni) !== null;
    }

    /**
     * Paso 19 (CU 25): Usuario con Fecha de Alta del día, cuenta "Activo", Localidad y Rol.
     *
     * @param  array<string, mixed>  $datos  datos personales + localidad_id
     */
    public function registrar(array $datos, int $rolId, string $password): User
    {
        $rol = $this->rolDelPersonal($rolId);

        return DB::transaction(function () use ($datos, $rol, $password) {
            $usuario = $this->usuarios->crearCuenta($datos, $rol->name, $password);

            // El panel exige correo verificado y el acceso es por correo (D12). Se envía solo si la transacción
            // confirma; un fallo del correo no revierte el alta.
            DB::afterCommit(fn () => $usuario->sendEmailVerificationNotification());

            return $usuario;
        });
    }

    /**
     * Paso 18 (CU 26): actualiza datos personales, Localidad y Rol. El DNI no se modifica.
     *
     * @param  array<string, mixed>  $datos
     */
    public function modificar(User $usuario, array $datos, int $rolId, User $actor): User
    {
        $rol = $this->rolDelPersonal($rolId);
        unset($datos['dni_usuario']);

        return DB::transaction(function () use ($usuario, $datos, $rol, $actor) {
            // El error de "propio rol" va en rol_id para que se vea debajo del campo del formulario.
            $usuario = $this->usuarios->actualizar($usuario, $datos, $rol->name, $actor, 'rol_id');

            if ($usuario->wasChanged('email')) {
                DB::afterCommit(fn () => $usuario->sendEmailVerificationNotification());
            }

            return $usuario;
        });
    }

    /** Pasos 14–15 (CU 27): cuenta "Inactivo" y Novedad con fecha de hoy, Motivo y Observación. */
    public function registrarBaja(User $usuario, int $motivoId, ?string $observacion, User $actor): Novedad
    {
        if ($usuario->is($actor)) {
            throw ValidationException::withMessages(['accion' => UsuarioService::MENSAJE_PROPIA_BAJA]);
        }

        $motivo = Motivo::query()->deAccion(Motivo::ACCION_BAJA_PERSONAL)->findOrFail($motivoId);

        return DB::transaction(function () use ($usuario, $motivo, $observacion) {
            $this->usuarios->desactivarCuenta($usuario);

            $novedad = new Novedad(['fecha_novedad' => today(), 'observacion_novedad' => $observacion]);
            $novedad->motivo()->associate($motivo);
            $usuario->novedades()->save($novedad);

            return $novedad;
        });
    }

    private function rolDelPersonal(int $rolId): Rol
    {
        return Rol::query()->delPersonal()->findOrFail($rolId);
    }
}
