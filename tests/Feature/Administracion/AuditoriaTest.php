<?php

namespace Tests\Feature\Administracion;

use App\Models\Materia;
use Spatie\Activitylog\Models\Activity;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

/** Módulo Auditoría (base común): listado con filtros y detalle de cambios de activity_log. */
class AuditoriaTest extends TestCase
{
    use ConRolesYPermisos;

    public function test_listado_filtrado_y_detalle_de_cambios(): void
    {
        $admin = $this->superAdmin();
        $this->actingAs($admin);
        $materia = Materia::factory()->create(['nombre_materia' => 'Matemática']);
        $materia->update(['nombre_materia' => 'Matemática I']);
        $modificacion = Activity::where('log_name', 'materias')->where('event', 'updated')->sole();

        $this->get(route('panel.auditoria.index'))->assertOk()->assertSee('Auditoría')->assertSee('materias');

        $filas = $this->post(route('panel.auditoria.datatable'), ['draw' => 1, 'start' => 0, 'length' => 10, 'modulo' => 'materias', 'evento' => 'updated'])
            ->assertOk()->json('data');
        $this->assertCount(1, $filas);
        $this->assertSame('Modificación', $filas[0]['evento']);
        $this->assertSame($admin->nombre_completo, $filas[0]['usuario']);

        $this->get(route('panel.auditoria.show', $modificacion->id))->assertOk()
            ->assertSee('nombre_materia')->assertSee('Matemática')->assertSee('Matemática I');
    }
}
