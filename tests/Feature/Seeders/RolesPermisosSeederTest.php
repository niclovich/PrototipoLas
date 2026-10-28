<?php

namespace Tests\Feature\Seeders;

use App\Enums\RolSistema;
use App\Models\Rol;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\RolesPermisosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class RolesPermisosSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_es_idempotente(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(52, Permission::count());
        $this->assertSame(count(RolSistema::cases()), Rol::count());
        $this->assertSame(1, User::count());
    }

    public function test_cada_rol_tiene_exactamente_los_permisos_del_plan(): void
    {
        $this->seed(RolesPermisosSeeder::class);

        // Matriz literal de docs/planes/00_general.md §2.2 (no se lee de config para que el test controle la config).
        $matriz = [
            'Director General' => [
                'listar_cuota', 'crear_cuota', 'editar_cuota',
                'listar_personal', 'crear_personal', 'editar_personal', 'eliminar_personal',
            ],
            'Coordinador' => [
                'listar_carrera', 'crear_carrera', 'editar_carrera',
                'listar_materia', 'crear_materia', 'editar_materia',
                'listar_plan_estudio', 'crear_plan_estudio', 'crear_materia_plan', 'eliminar_materia_plan',
                'crear_correlativa', 'listar_comision', 'crear_comision',
                'listar_acta_examen', 'crear_acta_examen', 'editar_acta_examen',
                'listar_cuota', 'crear_cuota', 'editar_cuota',
            ],
            'Preceptor' => [
                'listar_comision', 'listar_alumno', 'crear_preinscripcion', 'crear_usuario_alumno', 'crear_inscripcion',
                'crear_inscripcion_comision', 'editar_inscripcion_comision', 'crear_regularidad',
                'listar_acta_examen', 'crear_inscripcion_examen', 'exportar_inscripcion_examen', 'crear_asentamiento_acta',
            ],
            'Auxiliar Administrativo' => ['listar_alumno', 'crear_preinscripcion', 'crear_usuario_alumno', 'crear_inscripcion'],
            'Docente' => ['listar_comision', 'crear_regularidad'],
            'Cajero' => ['listar_alumno', 'listar_caja', 'crear_caja', 'editar_caja', 'crear_pago', 'exportar_pago'],
            'Alumno' => ['listar_alumno', 'crear_inscripcion_examen', 'exportar_inscripcion_examen'],
        ];

        $this->assertEqualsCanonicalizing([...array_keys($matriz), RolSistema::SuperAdmin->value], Rol::pluck('name')->all());

        foreach ($matriz as $rol => $esperados) {
            $reales = Rol::findByName($rol)->permissions->pluck('name')->all();

            $this->assertEqualsCanonicalizing($esperados, $reales, "Permisos de {$rol}");
        }

        $this->assertEqualsCanonicalizing(
            Permission::pluck('name')->all(),
            Rol::findByName(RolSistema::SuperAdmin->value)->permissions->pluck('name')->all(),
            'SUPERADMIN debe tener asignados todos los permisos existentes.',
        );
    }

    public function test_matriz_del_plan_general(): void
    {
        $this->seed(RolesPermisosSeeder::class);
        $permisos = fn (RolSistema $rol) => Rol::findByName($rol->value)->permissions->pluck('name');

        $this->assertCount(7, $permisos(RolSistema::DirectorGeneral));
        $this->assertCount(19, $permisos(RolSistema::Coordinador));
        $this->assertCount(12, $permisos(RolSistema::Preceptor));
        $this->assertCount(4, $permisos(RolSistema::AuxiliarAdministrativo));
        $this->assertCount(2, $permisos(RolSistema::Docente));
        $this->assertCount(6, $permisos(RolSistema::Cajero));
        $this->assertCount(3, $permisos(RolSistema::Alumno));
        $this->assertCount(Permission::count(), $permisos(RolSistema::SuperAdmin));

        $this->assertTrue($permisos(RolSistema::Cajero)->contains('crear_pago'));
        $this->assertFalse($permisos(RolSistema::Cajero)->contains('listar_carrera'));
        $this->assertTrue($permisos(RolSistema::Alumno)->contains('crear_inscripcion_examen'));
    }

    public function test_admin_inicial_es_superadmin_y_tiene_todo(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@las.test')->firstOrFail();

        $this->assertTrue($admin->hasRole(RolSistema::SuperAdmin->value));
        $this->assertTrue($admin->hasVerifiedEmail());
        $this->assertTrue($admin->can('listar_auditoria'));
        $this->assertTrue($admin->can('crear_pago'));
    }

    public function test_admin_seeder_exige_admin_password(): void
    {
        $this->seed(RolesPermisosSeeder::class);
        config(['las.admin.password' => null]);

        $this->expectException(RuntimeException::class);
        $this->seed(AdminSeeder::class);
    }
}
