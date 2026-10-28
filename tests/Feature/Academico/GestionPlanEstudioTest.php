<?php

namespace Tests\Feature\Academico;

use App\Enums\RolSistema;
use App\Models\Carrera;
use App\Models\Cuatrimestre;
use App\Models\DetallePlanEstudio;
use App\Models\Materia;
use App\Models\PlanEstudio;
use App\Models\User;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

/** P1 / P1c Gestión de Plan de Estudio (entrada de CU 5–8). */
class GestionPlanEstudioTest extends TestCase
{
    use ConRolesYPermisos;

    private User $coordinador;

    protected function setUp(): void
    {
        parent::setUp();
        $this->coordinador = $this->crearUsuario(RolSistema::Coordinador);
    }

    public function test_sin_plan_activo_muestra_p1c_y_el_combo_sin_inactivas(): void
    {
        Carrera::factory()->pendiente()->create(['nombre_carrera' => 'A Tecnicatura en Redes']);
        Carrera::factory()->inactiva()->create(['nombre_carrera' => 'Carrera Inactiva']);

        $this->actingAs($this->crearUsuario(RolSistema::Preceptor))->get(route('panel.planes.index'))->assertForbidden();
        $this->actingAs($this->coordinador)->get(route('panel.planes.index'))->assertOk()
            ->assertSeeInOrder(['GESTIÓN DE PLAN DE ESTUDIO', 'Seleccionar Carrera', 'A Tecnicatura en Redes',
                'Esta carrera no tiene un plan de estudio registrado', 'Registrar Plan de Estudio'])
            ->assertDontSee('Carrera Inactiva');
    }

    public function test_con_plan_activo_muestra_p1_y_la_tabla_lista_detalles_activos(): void
    {
        $plan = PlanEstudio::factory()->create(['anio_plan_estudio' => 2026]);
        $cuatri = Cuatrimestre::factory()->create(['cuatrimestre' => '1er Cuatrimestre']);
        $activo = DetallePlanEstudio::factory()->create([
            'plan_estudio_id' => $plan->id, 'cuatrimestre_id' => $cuatri->id, 'codigo_det_plan_estudio' => 'AM1',
            'carga_horaria_det_plan_estudio' => 96, 'materia_id' => Materia::factory()->create(['nombre_materia' => 'Análisis Matemático I'])->id,
        ]);
        DetallePlanEstudio::factory()->inactivo()->create(['plan_estudio_id' => $plan->id, 'codigo_det_plan_estudio' => 'BAJA1']);

        $this->actingAs($this->coordinador)->get(route('panel.planes.index', ['carrera' => $plan->carrera_id]))->assertOk()
            ->assertSeeInOrder(['Seleccionar Carrera', 'Año del Plan de Estudio', '2026', 'Generar Reporte', 'Registrar Materia',
                'Filtrar', 'Buscar por materia o código..', 'MATERIAS DEL PLAN', 'Materia', 'Código', 'Régimen', 'Cuatrimestre',
                'Año', 'Carga Hs.', 'Correlativas', 'Acciones']);

        $datos = $this->actingAs($this->coordinador)
            ->post(route('panel.planes.datatable'), ['carrera' => $plan->carrera_id, 'plan' => $plan->id, 'buscar' => 'análisis'])
            ->assertOk()->json('data');
        $this->assertCount(1, $datos);
        $this->assertSame(['Análisis Matemático I', 'AM1', '1C', '96 hs'], [$datos[0]['materia'], $datos[0]['codigo'], $datos[0]['cuatrimestre'], $datos[0]['carga']]);
        $this->assertStringContainsString(route('panel.planes.correlatividades.create', $activo->id), $datos[0]['correlativas']);

        $otro = PlanEstudio::factory()->create();
        $this->actingAs($this->coordinador)
            ->post(route('panel.planes.datatable'), ['carrera' => $plan->carrera_id, 'plan' => $otro->id])->assertOk()->assertJsonCount(0, 'data');
    }
}
