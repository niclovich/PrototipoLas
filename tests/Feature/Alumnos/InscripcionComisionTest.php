<?php

namespace Tests\Feature\Alumnos;

use App\Enums\CondicionCorrelativa;
use App\Enums\EstadoAlumno;
use App\Enums\EstadoAlumnoComision;
use App\Enums\EstadoLegajo;
use App\Enums\RolSistema;
use App\Enums\TipoInscripcion;
use App\Http\Controllers\Panel\Alumnos\AlumnoController;
use App\Http\Controllers\Panel\Alumnos\InscripcionComisionController;
use App\Mail\ComisionAsignada;
use App\Models\Alumno;
use App\Models\AlumnoComision;
use App\Models\Comision;
use App\Models\Correlativa;
use App\Models\Cuota;
use App\Models\DetallePagoCuota;
use App\Models\DetallePlanEstudio;
use App\Models\DetComision;
use App\Models\Mes;
use App\Models\Pago;
use App\Services\Alumnos\InscripcionComisionService;
use Carbon\CarbonImmutable;
use Database\Seeders\CatalogosSeeder;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

/** CU3 Inscripción en Comisiones (P2 → P3 → P4) y CU 19 Cambio de Comisión (P2 → Cambiar P3 → Confirmación). */
class InscripcionComisionTest extends TestCase
{
    use ConRolesYPermisos;

    private Alumno $alumno;

    private DetallePlanEstudio $matematica;

    private DetallePlanEstudio $programacion;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CatalogosSeeder::class);
        Mail::fake();
        $this->travelTo(CarbonImmutable::create((int) now()->year, 2, 15));

        $this->alumno = Alumno::factory()->create([
            'nro_legajo' => 12458, 'estado_alumno' => EstadoAlumno::Inscripto, 'estado_legajo' => EstadoLegajo::Activo,
        ]);
        $plan = $this->alumno->plan_estudio_id;
        $this->matematica = DetallePlanEstudio::factory()->create(['plan_estudio_id' => $plan, 'anio_curso_det_plan_estudio' => 1]);
        $this->programacion = DetallePlanEstudio::factory()->create(['plan_estudio_id' => $plan, 'anio_curso_det_plan_estudio' => 1]);
        Correlativa::factory()->create([
            'detalle_plan_estudio_id' => $this->programacion->id, 'detalle_plan_correlativa_id' => $this->matematica->id,
            'p_cursar' => CondicionCorrelativa::Regular, 'p_rendir' => null,
        ]);

        $inscripcion = Cuota::factory()->create(['carrera_id' => $this->alumno->carrera_id, 'mes_id' => Mes::inscripcion()->id, 'anio' => now()->year]);
        DetallePagoCuota::factory()->create(['cuota_id' => $inscripcion->id, 'pago_id' => Pago::factory()->create(['alumno_id' => $this->alumno->id])->id]);

        $this->actingAs($this->crearUsuario(RolSistema::Preceptor));
    }

    /** @param  list<DetallePlanEstudio>  $materias */
    private function comision(array $materias, array $atributos = []): Comision
    {
        $comision = Comision::factory()->create(['carrera_id' => $this->alumno->carrera_id, 'anio_curso' => 1, ...$atributos]);
        foreach ($materias as $materia) {
            DetComision::factory()->create(['comision_id' => $comision->id, 'detalle_plan_estudio_id' => $materia->id]);
        }

        return $comision;
    }

    private function inscribirEn(Comision $comision, ?Alumno $alumno = null, array $atributos = []): void
    {
        $alumno ??= $this->alumno;
        foreach ($comision->detalles as $det) {
            AlumnoComision::factory()->create(['alumno_id' => $alumno->id, 'det_comision_id' => $det->id, ...$atributos]);
        }
        $alumno->update(['comision_actual_id' => $comision->id]);
    }

    public function test_camino_feliz_inscripcion(): void
    {
        $comision = $this->comision([$this->matematica, $this->programacion]);

        $this->get(route('panel.alumno-comisiones.index', $this->alumno))->assertOk()->assertSee('HISTORIAL DE COMISIONES')->assertSee('Nueva Comision');
        $this->get(route('panel.alumno-comisiones.create', [$this->alumno, 'comision_id' => $comision->id]))->assertOk()
            ->assertSee(InscripcionComisionService::HABILITADA)
            ->assertSee('No asignable: requiere '.$this->matematica->materia->nombre_materia.' Regular')
            ->assertDontSee('data-abrir', false);

        $this->post(route('panel.alumno-comisiones.store', $this->alumno), ['comision_id' => $comision->id])
            ->assertRedirect(route('panel.alumno-comisiones.confirmacion'));

        $inscripciones = AlumnoComision::where('alumno_id', $this->alumno->id)->get();
        $this->assertCount(1, $inscripciones);
        $this->assertSame(EstadoAlumnoComision::Activo, $inscripciones[0]->estado_alumno_comision);
        $this->assertSame(TipoInscripcion::PorComision, $inscripciones[0]->tipo_inscripcion);
        $this->assertSame($comision->id, $this->alumno->refresh()->comision_actual_id);
        Mail::assertQueued(ComisionAsignada::class);

        $this->get(route('panel.alumno-comisiones.confirmacion'))->assertOk()
            ->assertSee(InscripcionComisionService::CONFIRMACION)->assertSee('Materias asignadas: 1');
    }

    public function test_cuotas_pendientes(): void
    {
        Cuota::factory()->create(['carrera_id' => $this->alumno->carrera_id, 'mes_id' => Mes::where('descripcion', 'Marzo')->value('id'), 'anio' => now()->year]);
        $this->travelTo(CarbonImmutable::create((int) now()->year, 3, 20));

        $this->get(route('panel.alumno-comisiones.index', $this->alumno))
            ->assertRedirect(route('panel.alumnos.index'))
            ->assertSessionHas(AlumnoController::AVISO, InscripcionComisionService::MENSAJE_CUOTAS_PENDIENTES)
            ->assertSessionHas(AlumnoController::AVISO_LISTA, ['Cuota Marzo '.now()->year]);
    }

    public function test_comision_excede_su_capacidad(): void
    {
        $comision = $this->comision([$this->matematica], ['capacidad_comision' => 1]);
        $this->inscribirEn($comision, Alumno::factory()->create(['carrera_id' => $this->alumno->carrera_id]));

        $this->get(route('panel.alumno-comisiones.create', [$this->alumno, 'comision_id' => $comision->id]))->assertOk()
            ->assertSee(InscripcionComisionService::MENSAJE_CAPACIDAD)->assertSee('data-abrir', false);

        $this->post(route('panel.alumno-comisiones.store', $this->alumno), ['comision_id' => $comision->id])
            ->assertSessionHas(InscripcionComisionController::AVISO, InscripcionComisionService::MENSAJE_CAPACIDAD);
        $this->assertSame(0, AlumnoComision::where('alumno_id', $this->alumno->id)->count());
    }

    public function test_ninguna_materia_asignable(): void
    {
        $comision = $this->comision([$this->programacion]);

        $this->get(route('panel.alumno-comisiones.create', [$this->alumno, 'comision_id' => $comision->id]))->assertOk()
            ->assertSee(InscripcionComisionService::MENSAJE_SIN_MATERIAS)->assertSee('data-abrir', false);

        $this->post(route('panel.alumno-comisiones.store', $this->alumno), ['comision_id' => $comision->id])
            ->assertSessionHas(InscripcionComisionController::AVISO, InscripcionComisionService::MENSAJE_SIN_MATERIAS);
    }

    public function test_camino_feliz_cambio_de_comision(): void
    {
        $origen = $this->comision([$this->matematica], ['codigo_comision' => 'COM-1A']);
        $destino = $this->comision([$this->matematica], ['codigo_comision' => 'COM-1B']);
        $this->inscribirEn($origen);

        $this->get(route('panel.alumno-comisiones.index', $this->alumno))->assertOk()
            ->assertSee(route('panel.alumno-comisiones.cambio', $this->alumno));
        $this->get(route('panel.alumno-comisiones.cambio', [$this->alumno, 'comision_id' => $destino->id]))->assertOk()
            ->assertSee('Comision Actual')->assertSee('COM-1B')->assertSee(InscripcionComisionService::HABILITADA);

        $this->put(route('panel.alumno-comisiones.cambio.update', $this->alumno), ['comision_id' => $destino->id])
            ->assertRedirect(route('panel.alumno-comisiones.cambio.confirmacion'));

        $this->assertDatabaseHas('alumno_comision', ['alumno_id' => $this->alumno->id, 'det_comision_id' => $origen->detalles[0]->id, 'estado_alumno_comision' => 'Caducado']);
        $this->assertDatabaseHas('alumno_comision', ['alumno_id' => $this->alumno->id, 'det_comision_id' => $destino->detalles[0]->id, 'estado_alumno_comision' => 'Activo']);
        $this->assertSame($destino->id, $this->alumno->refresh()->comision_actual_id);
        Mail::assertQueued(ComisionAsignada::class);

        $this->get(route('panel.alumno-comisiones.cambio.confirmacion'))->assertOk()
            ->assertSee(InscripcionComisionService::CONFIRMACION_CAMBIO)->assertSee('Materias transferidas: 1');
    }

    public function test_cambio_a_comision_sin_capacidad(): void
    {
        $origen = $this->comision([$this->matematica]);
        $destino = $this->comision([$this->matematica], ['capacidad_comision' => 1]);
        $this->inscribirEn($origen);
        $this->inscribirEn($destino, Alumno::factory()->create(['carrera_id' => $this->alumno->carrera_id]));

        $this->get(route('panel.alumno-comisiones.cambio', [$this->alumno, 'comision_id' => $destino->id]))->assertOk()
            ->assertSee(InscripcionComisionService::MENSAJE_CAPACIDAD)->assertSee('data-abrir', false);

        $this->put(route('panel.alumno-comisiones.cambio.update', $this->alumno), ['comision_id' => $destino->id])
            ->assertSessionHas(InscripcionComisionController::AVISO, InscripcionComisionService::MENSAJE_CAPACIDAD);
        $this->assertSame($origen->id, $this->alumno->refresh()->comision_actual_id);
    }

    public function test_cambio_bloqueado_con_evaluaciones_registradas(): void
    {
        $origen = $this->comision([$this->matematica]);
        $this->comision([$this->matematica]);
        $this->inscribirEn($origen, null, ['nota_p1' => 8]);

        $this->get(route('panel.alumno-comisiones.cambio', $this->alumno))
            ->assertRedirect(route('panel.alumno-comisiones.index', $this->alumno))
            ->assertSessionHas(InscripcionComisionController::AVISO, InscripcionComisionService::MENSAJE_CON_EVALUACIONES);
    }
}
