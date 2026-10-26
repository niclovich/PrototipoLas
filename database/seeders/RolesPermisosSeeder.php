<?php

namespace Database\Seeders;

use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Models\Rol;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Roles (actores de la tesis + SUPERADMIN) y permisos {accion}_{entidad} de config/permisos.php
 * (plan general §2.2). Idempotente: se puede correr varias veces sin duplicar.
 */
class RolesPermisosSeeder extends Seeder
{
    public function run(PermissionRegistrar $registrar): void
    {
        $registrar->forgetCachedPermissions();

        foreach (config('permisos.permisos') as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
        }

        foreach (RolSistema::cases() as $rolSistema) {
            $rol = Rol::firstOrCreate(
                ['name' => $rolSistema->value, 'guard_name' => 'web'],
                ['estado' => EstadoGeneral::Activo],
            );
            $rol->update(['descripcion' => $rolSistema->descripcion()]);
            $rol->syncPermissions(config("permisos.roles.{$rolSistema->value}", []));
        }

        $registrar->forgetCachedPermissions();
    }
}
