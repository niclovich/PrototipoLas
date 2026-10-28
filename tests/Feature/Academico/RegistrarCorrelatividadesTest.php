<?php

namespace Tests\Feature\Academico;

use App\Enums\CondicionCorrelativa;
use App\Enums\RolSistema;
use App\Models\Correlativa;
use App\Models\DetallePlanEstudio;
use App\Models\Materia;
use App\Models\PlanEstudio;
use App\Models\User;
use App\Services\Academico\CorrelatividadService as Svc;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

/** CU 8 Registrar Correlatividades de Materia (sin escenarios alternativos en la especificación). */
class RegistrarCorrelatividadesTest extends TestCase
{
    use ConRolesYPermisos;

    private User $coordinador;

    private PlanEstudio $plan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->coordinador = $this->crearUsuario(RolSistema::Coordinador);
        $this->plan = PlanEstudio::factory()->create(['anio_plan_estudio' => 2026]);
    }

    private function detalle(string $nombre, string $codigo, int $anio): DetallePlanEstudio
    {
        return DetallePlanEstudio::factory()->create([
            'plan_estudio_id' => $this->plan->id, 'codigo_det_plan_estudio' => $codigo, 'anio_curso_det_plan_estudio' => $anio,
            'materia_id' => Materia::factory()->create(['nombre_materia' => $nombre])->id,
        ]);
    }

    public function test_registra_las_correlatividades_de_la_tabla(): void
    {
        $am1 = $this->detalle('Análisis Matemático I', 'AM1', 1);
        $alg = $this->detalle('Álgebra', 'ALG', 1);
        $prog = $this->detalle('Programación I', 'PR1', 1);
        $am2 = $this->detalle('Análisis Matemático II', 'AM2', 2);
        $this->detalle('Programación III', 'PR3', 3);
        Correlativa::factory()->create(['detalle_plan_estudio_id' => $am2->id, 'detalle_plan_correlativa_id' => $prog->id]);

        $this->actingAs($this->coordinador)->get(route('panel.planes.correlatividades.create', $am2->id))->assertOk()
            ->assertSeeInOrder(['Registrar Correlatividades de Materia', 'Datos de la Materia', 'Análisis Matemático II', 'AM2',
                '1 · Agregar Correlativa', 'Materia Correlativa', 'Para Cursar', 'R - Regular', 'Para Rendir', '+ Agregar',
                'Correlativas de la Materia', 'Materia Correlativa', 'Código', 'Para Cursar', 'Para Rendir', 'Acción',
                'Programación I', 'R (Regular)', 'A (Aprobada)', 'Cancelar', 'Registrar Correlatividades'])
            ->assertDontSee('Programación III');

        // Se agregan AM1 y Álgebra y se quita Programación I (🗑).
        $respuesta = $this->actingAs($this->coordinador)->post(route('panel.planes.correlatividades.store', $am2->id), ['correlativas' => [
            ['detalle_plan_correlativa_id' => $am1->id, 'p_cursar' => 'Regular', 'p_rendir' => 'Aprobada'],
            ['detalle_plan_correlativa_id' => $alg->id, 'p_cursar' => 'Regular', 'p_rendir' => 'Aprobada'],
        ]]);
        $this->followRedirects($respuesta)->assertSeeInOrder([
            Svc::CONFIRMACION_REGISTRO, 'Plan: 2026', 'Materia: Análisis Matemático II · Código: AM2',
            'Correlativas: Análisis Matemático I (Cursar: R · Rendir: A)', 'Álgebra (Cursar: R · Rendir: A)', 'Aceptar',
        ]);

        $this->assertEqualsCanonicalizing([$am1->id, $alg->id], Correlativa::where('detalle_plan_estudio_id', $am2->id)->pluck('detalle_plan_correlativa_id')->all());
        $this->assertSame(CondicionCorrelativa::Aprobada, Correlativa::where('detalle_plan_correlativa_id', $am1->id)->sole()->p_rendir);
    }

    public function test_rechaza_correlativa_de_un_anio_igual_o_posterior(): void
    {
        $am2 = $this->detalle('Análisis Matemático II', 'AM2', 2);
        $otra = $this->detalle('Física II', 'FI2', 2);

        $this->actingAs($this->coordinador)->post(route('panel.planes.correlatividades.store', $am2->id), ['correlativas' => [
            ['detalle_plan_correlativa_id' => $otra->id, 'p_cursar' => 'Regular', 'p_rendir' => 'Aprobada'],
        ]])->assertSessionHasErrors(['correlativas.0.detalle_plan_correlativa_id' => Svc::MENSAJE_CORRELATIVA_INVALIDA]);
        $this->assertSame(0, Correlativa::count());
    }
}
