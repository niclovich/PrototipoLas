<?php

namespace Tests\Feature\Administracion;

use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Models\Rol;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

class RolesTest extends TestCase
{
    use ConRolesYPermisos;

    public function test_index_y_datatable(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->get(route('panel.roles.index'))->assertOk()->assertSee('Roles y Permisos');

        $filas = $this->actingAs($admin)->postJson(route('panel.roles.datatable'), ['draw' => 1, 'start' => 0, 'length' => 25, 'pestania' => 'activos'])
            ->assertOk()->json('data');

        $this->assertCount(count(RolSistema::cases()), $filas);
    }

    public function test_crear_rol_con_permisos(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->get(route('panel.roles.create'))->assertOk()->assertSee('listar_auditoria');
        $this->actingAs($admin)->post(route('panel.roles.store'), [
            'name' => 'Auditor',
            'descripcion' => 'Consulta la auditoría',
            'permisos' => ['listar_auditoria', 'ver_auditoria'],
        ])->assertRedirect(route('panel.roles.index'));

        $rol = Rol::findByName('Auditor');
        $this->assertSame(['listar_auditoria', 'ver_auditoria'], $rol->permissions->pluck('name')->sort()->values()->all());
    }

    public function test_rechaza_permisos_inexistentes(): void
    {
        $this->actingAs($this->superAdmin())->post(route('panel.roles.store'), ['name' => 'X', 'permisos' => ['inventado']])
            ->assertSessionHasErrors('permisos.0');
    }

    public function test_rol_del_sistema_no_cambia_de_nombre_ni_se_da_de_baja(): void
    {
        $admin = $this->superAdmin();
        $cajero = Rol::findByName(RolSistema::Cajero->value);

        $this->actingAs($admin)->put(route('panel.roles.update', $cajero), [
            'name' => 'Otro nombre',
            'descripcion' => 'Nueva descripción',
            'permisos' => ['listar_caja'],
        ])->assertRedirect(route('panel.roles.index'));

        $cajero->refresh();
        $this->assertSame(RolSistema::Cajero->value, $cajero->name);
        $this->assertSame('Nueva descripción', $cajero->descripcion);

        $this->actingAs($admin)->delete(route('panel.roles.destroy', $cajero))
            ->assertSessionHasErrors(['accion' => 'Los roles del sistema no se pueden dar de baja.']);
    }

    public function test_baja_y_reactivacion_de_rol_propio(): void
    {
        $admin = $this->superAdmin();
        $rol = Rol::create(['name' => 'Temporal', 'guard_name' => 'web']);

        $this->actingAs($admin)->delete(route('panel.roles.destroy', $rol))->assertSessionHas('success');
        $this->assertSame(EstadoGeneral::Inactivo, $rol->fresh()->estado);

        $this->actingAs($admin)->patch(route('panel.roles.reactivar', $rol))->assertSessionHas('success');
        $this->assertSame(EstadoGeneral::Activo, $rol->fresh()->estado);
    }
}
