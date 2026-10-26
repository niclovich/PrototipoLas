<?php

namespace Database\Seeders;

use App\Enums\EstadoCuenta;
use App\Enums\RolSistema;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Usuario SUPERADMIN inicial desde ADMIN_* del .env (config/las.php). Idempotente.
 */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = config('las.admin');

        if (blank($admin['password'])) {
            throw new RuntimeException('Defina ADMIN_PASSWORD en el archivo .env para crear el usuario administrador.');
        }

        $usuario = User::firstOrNew(['email' => mb_strtolower($admin['email'])]);

        $usuario->fill([
            'dni_usuario' => $admin['dni'],
            'apellido_usuario' => $admin['apellido'],
            'nombre_usuario' => $admin['nombre'],
        ]);
        $usuario->forceFill(['estado_cuenta' => EstadoCuenta::Activo]);

        if (! $usuario->exists) {
            $usuario->forceFill([
                'password' => $admin['password'],
                'fecha_alta' => today(),
                'email_verified_at' => now(),
            ]);
        }

        $usuario->save();
        $usuario->syncRoles([RolSistema::SuperAdmin->value]);
    }
}
