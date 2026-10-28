<?php

namespace Tests\Feature\Academico;

use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Models\Carrera;
use App\Models\Cuatrimestre;
use App\Models\DetallePlanEstudio;
use App\Models\Duracion;
use App\Models\Materia;
use App\Models\PlanEstudio;
use App\Models\Regimen;
use App\Models\User;
use App\Services\Academico\DetallePlanEstudioService as Svc;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

/** CU 6 Registrar Materia al Plan de Estudio. */
class RegistrarMateriaPlanTest extends TestCase
{
    use ConRolesYPermisos;

    private User $coordinador;

    private PlanEstudio $plan;

    private array $datos;

    protected function setUp(): void
    {
        parent::setUp();
        $this->coordinador = $this->crearUsuario(RolSistema::Coordinador);
        $carrera = Carrera::factory()->create([
            'nombre_carrera' => 'Licenciatura en Análisis de Sistemas',
            'duracion_id' => Duracion::factory()->create(['cantidad_anios' => 3])->id,
        ]);
        $this->plan = PlanEstudio::factory()->create(['carrera_id' => $carrera->id]);
        $this->datos = [
            'materia_id' => Materia::factory()->create(['nombre_materia' => 'Base de Datos I'])->id,
            'codigo_det_plan_estudio' => ' BD1 ',
            'regimen_id' => Regimen::factory()->create(['regimen' => 'Cuatrimestral'])->id,
            'cuatrimestre_id' => Cuatrimestre::factory()->create(['cuatrimestre' => '1er Cuatrimestre'])->id,
            'anio_curso_det_plan_estudio' => 1,
            'carga_horaria_det_plan_estudio' => 96,
        ];
    }

    private function registrar(array $cambios = [])
    {
        return $this->actingAs($this->coordinador)->from(route('panel.planes.materias.create', $this->plan))
            ->post(route('panel.planes.materias.store', $this->plan), [...$this->datos, ...$cambios]);
    }

    public function test_registra_la_materia_activa_y_muestra_la_confirmacion(): void
    {
        $this->actingAs($this->coordinador)->get(route('panel.planes.materias.create', $this->plan))->assertOk()
            ->assertSeeInOrder(['Registrar Materia al Plan', '1 · Datos de la Materia', 'Carrera', 'Materia', 'Código de Materia',
                'Régimen', 'Cuatrimestre', 'Año de Dictado', 'Carga Horaria', 'Cancelar', 'Registrar Materia']);

        $this->followRedirects($this->registrar(['estado_det_plan_estudio' => 'Inactivo']))->assertSeeInOrder([
            Svc::CONFIRMACION_REGISTRO, 'Carrera: Licenciatura en Análisis de Sistemas', 'Materia: Base de Datos I · Código: BD1',
            'Régimen: Cuatrimestral · Cuatrimestre: 1C · Carga Horaria: 96 hs', 'Aceptar',
        ]);

        $detalle = DetallePlanEstudio::sole();
        $this->assertSame([$this->plan->id, 'BD1', EstadoGeneral::Activo], [$detalle->plan_estudio_id, $detalle->codigo_det_plan_estudio, $detalle->estado_det_plan_estudio]);
    }

    public function test_alt_8_materia_ya_cargada(): void
    {
        DetallePlanEstudio::factory()->inactivo()->create(['plan_estudio_id' => $this->plan->id, 'materia_id' => $this->datos['materia_id']]);

        $this->registrar()->assertSessionHasErrors(['materia_id' => 'La materia ya se encuentra cargada en el Plan de Estudio']);
        $this->followRedirects($this->registrar())->assertSee('Materia ya cargada')->assertSee('data-foco="#materia_id"', false);
    }

    public function test_alt_11_codigo_existente(): void
    {
        DetallePlanEstudio::factory()->create(['plan_estudio_id' => $this->plan->id, 'codigo_det_plan_estudio' => 'BD1']);

        $this->registrar(['codigo_det_plan_estudio' => 'bd1'])
            ->assertSessionHasErrors(['codigo_det_plan_estudio' => 'Código ya existente en el Plan de Estudio']);
        $this->followRedirects($this->registrar())->assertSee('Código existente');
    }

    public function test_alt_23_carga_horaria_invalida(): void
    {
        foreach (['', 0, 1000, 'abc', 9.5] as $carga) {
            $this->registrar(['carga_horaria_det_plan_estudio' => $carga])
                ->assertSessionHasErrors(['carga_horaria_det_plan_estudio' => 'La carga horaria ingresada no es válida']);
        }
        $this->followRedirects($this->registrar(['carga_horaria_det_plan_estudio' => 0]))->assertSee('Carga horaria inválida');
        $this->assertSame(0, DetallePlanEstudio::count());
    }
}
