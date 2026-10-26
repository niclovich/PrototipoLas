<?php

namespace App\Services\Usuarios;

use App\Enums\EstadoGeneral;
use App\Models\Rol;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;

/**
 * ABM de roles (clase Rol de la tesis) y asignación de permisos.
 */
class RolService
{
    public function __construct(private readonly PermissionRegistrar $registrar) {}

    /**
     * @param  array{name: string, descripcion: ?string}  $datos
     * @param  list<string>  $permisos
     */
    public function crear(array $datos, array $permisos): Rol
    {
        return $this->guardar(new Rol(['guard_name' => 'web', 'estado' => EstadoGeneral::Activo]), $datos, $permisos);
    }

    /**
     * @param  array{name?: string, descripcion: ?string}  $datos
     * @param  list<string>  $permisos
     */
    public function actualizar(Rol $rol, array $datos, array $permisos): Rol
    {
        if ($rol->esDelSistema()) {
            unset($datos['name']);
        }

        return $this->guardar($rol, $datos, $permisos);
    }

    public function darDeBaja(Rol $rol): void
    {
        if ($rol->esDelSistema()) {
            throw ValidationException::withMessages(['accion' => 'Los roles del sistema no se pueden dar de baja.']);
        }

        if ($rol->users()->exists()) {
            throw ValidationException::withMessages(['accion' => 'No se puede dar de baja un rol con usuarios asignados.']);
        }

        $rol->update(['estado' => EstadoGeneral::Inactivo]);
        $this->registrar->forgetCachedPermissions();
    }

    public function reactivar(Rol $rol): void
    {
        $rol->update(['estado' => EstadoGeneral::Activo]);
        $this->registrar->forgetCachedPermissions();
    }

    /**
     * @param  array<string, mixed>  $datos
     * @param  list<string>  $permisos
     */
    private function guardar(Rol $rol, array $datos, array $permisos): Rol
    {
        DB::transaction(function () use ($rol, $datos, $permisos) {
            $rol->fill($datos)->save();
            $rol->syncPermissions($permisos);
        });

        $this->registrar->forgetCachedPermissions();

        return $rol;
    }
}
