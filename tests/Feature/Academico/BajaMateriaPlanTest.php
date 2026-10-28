<?php

namespace Tests\Feature\Academico;

use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Models\Comision;
use App\Models\DetallePlanEstudio;
use App\Models\DetComision;
use App\Models\User;
use App\Services\Academico\DetallePlanEstudioService as Svc;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

/** CU 7 Registrar Baja de Materia del Plan de Estudio. */
class BajaMateriaPlanTest extends TestCase
{
    use ConRolesYPermisos;

    private User $coordinador;

    private DetallePlanEstudio $detalle;

    protected function setUp(): void
    {
        parent::setUp();
        $this->coordinador = $this->crearUsuario(RolSistema::Coordinador);
        $this->detalle = DetallePlanEstudio::factory()->create(['codigo_det_plan_estudio' => 'AM1']);
    }

    private function darDeBaja()
    {
        return $this->actingAs($this->coordinador)->post(route('panel.planes.materias.baja', $this->detalle->id));
    }

    public function test_da_de_baja_la_materia_y_muestra_la_confirmacion(): void
    {
        // Una comisión Inactivo no impide la baja (E7-D3).
        DetComision::factory()->create(['detalle_plan_estudio_id' => $this->detalle->id,
            'comision_id' => Comision::factory()->create(['estado_comision' => EstadoGeneral::Inactivo])->id]);

        $this->followRedirects($this->darDeBaja())->assertSeeInOrder([
            Svc::CONFIRMACION_BAJA, 'Código: AM1', 'Fecha de Baja: '.today()->format('d/m/Y'), 'Aceptar',
        ]);

        $this->detalle->refresh();
        $this->assertSame(EstadoGeneral::Inactivo, $this->detalle->estado_det_plan_estudio);
        $this->assertTrue($this->detalle->fecha_baja->isToday());
        $this->darDeBaja()->assertNotFound();
    }

    public function test_alt_6_no_da_de_baja_con_comisiones_dictandose(): void
    {
        DetComision::factory()->create(['detalle_plan_estudio_id' => $this->detalle->id]);

        $this->followRedirects($this->darDeBaja())
            ->assertSee('No se puede dar de baja la materia porque posee comisiones dictándose')->assertSee('Materia con comisiones');
        $this->assertSame(EstadoGeneral::Activo, $this->detalle->refresh()->estado_det_plan_estudio);

        $datos = $this->actingAs($this->coordinador)->post(route('panel.planes.datatable'), [
            'carrera' => $this->detalle->planEstudio->carrera_id, 'plan' => $this->detalle->plan_estudio_id,
        ])->json('data');
        $this->assertStringContainsString('&quot;comisiones&quot;:true', $datos[0]['acciones']);
    }
}
