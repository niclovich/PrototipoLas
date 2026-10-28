<?php

namespace App\Services\Usuarios;

use App\Enums\EstadoCuenta;
use App\Enums\RolSistema;
use App\Models\Rol;
use App\Models\User;
use App\Notifications\DefinirContrasenia;
use App\Support\DatosUsuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Alta, modificación, baja y reactivación de usuarios (base común) y
 * link firmado de un solo uso para definir la contraseña (plan §5.4).
 */
class UsuarioService
{
    public const MENSAJE_SOLO_SUPERADMIN = 'Solo un SUPERADMIN puede asignar o modificar el rol SUPERADMIN.';

    public const MENSAJE_PROPIO_ROL = 'No puede cambiar su propio rol.';

    public const MENSAJE_PROPIA_BAJA = 'No puede dar de baja su propio usuario.';

    /** CU 25 alt. 4.1; lo usan también Usuarios Alumno (CU 9) y CU1. */
    public const MENSAJE_DNI_REGISTRADO = 'Ya existe un usuario registrado con el DNI ingresado';

    /** Correo único (el acceso es por correo): Personal (D10 de E2) y Usuarios Alumno (CU 9). */
    public const MENSAJE_CORREO_REGISTRADO = 'Ya existe un usuario registrado con el correo ingresado.';

    /** Usuario.buscar(dni): cualquier usuario (personal, alumno o inactivo) con ese DNI, normalizado a dígitos. */
    public function buscarPorDni(string $dni): ?User
    {
        $soloDigitos = DatosUsuario::normalizarDni($dni);

        return $soloDigitos === '' ? null : User::query()->where('dni_usuario', $soloDigitos)->first();
    }

    /**
     * @param  array{dni_usuario: string, apellido_usuario: string, nombre_usuario: string, email: string}  $datos
     */
    public function crear(array $datos, string $rol, User $actor): User
    {
        $this->validarRolAsignable($rol, $actor);

        $usuario = DB::transaction(fn () => $this->crearCuenta($datos, $rol, null));

        $this->enviarLinkAlta($usuario);

        return $usuario;
    }

    /**
     * Crea la cuenta (Activo, fecha de alta hoy) con un rol. Sin contraseña (alta por link) o con la que carga
     * el Director General (CU 25). Debe llamarse dentro de una transacción.
     *
     * @param  array<string, mixed>  $datos  atributos fillable de User
     */
    public function crearCuenta(array $datos, string $rol, ?string $password): User
    {
        $usuario = new User($datos);
        $usuario->forceFill([
            'password' => $password,
            'fecha_alta' => today(),
            'estado_cuenta' => EstadoCuenta::Activo,
        ])->save();
        $usuario->syncRoles([$rol]);

        return $usuario;
    }

    /**
     * @param  array{dni_usuario: string, apellido_usuario: string, nombre_usuario: string, email: string}  $datos
     */
    public function actualizar(User $usuario, array $datos, string $rol, User $actor, string $campoRol = 'rol'): User
    {
        $this->validarGestion($usuario, $actor);
        $this->validarRolAsignable($rol, $actor);

        if ($usuario->is($actor) && $usuario->nombreRol() !== $rol) {
            throw ValidationException::withMessages([$campoRol => self::MENSAJE_PROPIO_ROL]);
        }

        return DB::transaction(function () use ($usuario, $datos, $rol) {
            $usuario->fill($datos);

            if ($usuario->isDirty('email')) {
                $usuario->email_verified_at = null;
            }

            $usuario->save();
            $usuario->syncRoles([$rol]);

            return $usuario;
        });
    }

    public function darDeBaja(User $usuario, User $actor): void
    {
        if ($usuario->is($actor)) {
            throw ValidationException::withMessages(['accion' => self::MENSAJE_PROPIA_BAJA]);
        }

        $this->validarGestion($usuario, $actor);

        DB::transaction(fn () => $this->desactivarCuenta($usuario));
    }

    /**
     * Cuenta "Inactivo" y corta el acceso ya: invalida "Recordarme" y borra las sesiones abiertas
     * (además del middleware cuenta.activa). Lo usan la baja de Usuarios y la de Personal (CU 27).
     */
    public function desactivarCuenta(User $usuario): void
    {
        $usuario->forceFill([
            'estado_cuenta' => EstadoCuenta::Inactivo,
            'remember_token' => Str::random(60),
        ])->save();

        DB::table(config('session.table', 'sessions'))->where('user_id', $usuario->getKey())->delete();
    }

    public function reactivar(User $usuario, User $actor): void
    {
        $this->validarGestion($usuario, $actor);

        $usuario->forceFill(['estado_cuenta' => EstadoCuenta::Activo])->save();
    }

    public function enviarLinkAlta(User $usuario): void
    {
        $horas = config('las.link_alta_horas');

        $usuario->notify(new DefinirContrasenia($this->generarLinkAlta($usuario, $horas), $horas));
    }

    public function generarLinkAlta(User $usuario, int $horas): string
    {
        return URL::temporarySignedRoute(
            'password.definir',
            now()->addHours($horas),
            ['user' => $usuario->getKey(), 'hash' => sha1($usuario->email)],
        );
    }

    /** Un solo uso: el link deja de servir cuando el usuario ya tiene contraseña o cambió el correo. */
    public function linkVigente(User $usuario, string $hash): bool
    {
        return $usuario->password === null
            && $usuario->estaActivo()
            && hash_equals(sha1($usuario->email), $hash);
    }

    public function definirContrasenia(User $usuario, string $password): void
    {
        $usuario->forceFill([
            'password' => $password,
            // El link llegó al correo del usuario: eso verifica el correo.
            'email_verified_at' => $usuario->email_verified_at ?? now(),
        ])->save();
    }

    /**
     * Roles que el actor puede asignar desde Usuarios: activos, sin Alumno, y SUPERADMIN solo si el actor lo es.
     *
     * @return array<string, string>
     */
    public function rolesAsignables(User $actor): array
    {
        $excluidos = [RolSistema::Alumno->value];

        if (! $actor->tieneRol(RolSistema::SuperAdmin)) {
            $excluidos[] = RolSistema::SuperAdmin->value;
        }

        return Rol::query()->activos()
            ->whereNotIn('name', $excluidos)
            ->orderBy('name')
            ->pluck('name', 'name')
            ->all();
    }

    /** Un usuario que no es SUPERADMIN no puede dar ni quitar el rol SUPERADMIN (evita escalar privilegios). */
    private function validarRolAsignable(string $rol, User $actor): void
    {
        if ($rol === RolSistema::SuperAdmin->value && ! $actor->tieneRol(RolSistema::SuperAdmin)) {
            throw ValidationException::withMessages(['rol' => self::MENSAJE_SOLO_SUPERADMIN]);
        }
    }

    private function validarGestion(User $usuario, User $actor): void
    {
        if ($usuario->tieneRol(RolSistema::SuperAdmin) && ! $actor->tieneRol(RolSistema::SuperAdmin)) {
            throw ValidationException::withMessages(['accion' => self::MENSAJE_SOLO_SUPERADMIN]);
        }
    }
}
