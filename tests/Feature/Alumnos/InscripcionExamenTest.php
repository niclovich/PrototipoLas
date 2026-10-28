<?php

namespace Tests\Feature\Alumnos;

use App\Enums\CondicionCorrelativa;
use App\Enums\CondicionCurricular;
use App\Enums\EstadoAlumno;
use App\Enums\EstadoDetalleActa;
use App\Enums\EstadoLegajo;
use App\Enums\RolSistema;
use App\Http\Controllers\Panel\Alumnos\AlumnoController;
use App\Mail\InscripcionExamenRegistrada;
use App\Models\ActaExamen;
use App\Models\Alumno;
use App\Models\Correlativa;
use App\Models\Cuota;
use App\Models\DetalleActa;
use App\Models\DetallePagoCuota;
use App\Models\DetallePlanEstudio;
use App\Models\EstadoCurricular;
use App\Models\Mes;
use App\Models\Pago;
use App\Models\TipoActa;
use App\Models\User;
use App\Services\Examenes\InscripcionExamenService;
use Carbon\CarbonImmutable;
use Database\Seeders\CatalogosSeeder;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

/** CU5 Inscripción a Exámenes: P1 → P2 Actas de Examen Disponibles → P3 Confirmación + comprobante con QR. */
class InscripcionExamenTest extends TestCase
{
    use ConRolesYPermisos;

    private Alumno $alumno;

    private DetallePlanEstudio $matematica;

    private DetallePlanEstudio $analisis;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CatalogosSeeder::class);
        Mail::fake();
        $this->travelTo(CarbonImmutable::create((int) now()->year, 2, 15));

        $usuario = $this->crearUsuario(RolSistema::Alumno);
        $this->alumno = Alumno::factory()->create([
            'user_id' => $usuario->id, 'nro_legajo' => 12458,
            'estado_alumno' => EstadoAlumno::Inscripto, 'estado_legajo' => EstadoLegajo::Activo,
        ]);
        $plan = $this->alumno->plan_estudio_id;
        $this->matematica = DetallePlanEstudio::factory()->create(['plan_estudio_id' => $plan, 'anio_curso_det_plan_estudio' => 1]);
        $this->analisis = DetallePlanEstudio::factory()->create(['plan_estudio_id' => $plan, 'anio_curso_det_plan_estudio' => 2]);
        Correlativa::factory()->create([
            'detalle_plan_estudio_id' => $this->analisis->id, 'detalle_plan_correlativa_id' => $this->matematica->id,
            'p_cursar' => null, 'p_rendir' => CondicionCorrelativa::Aprobada,
        ]);

        $this->actingAs($this->crearUsuario(RolSistema::Preceptor));
    }

    private function acta(DetallePlanEstudio $materia, string $tipo = 'Regular'): ActaExamen
    {
        return ActaExamen::factory()->create([
            'detalle_plan_estudio_id' => $materia->id,
            'tipo_acta_id' => TipoActa::where('descripcion', $tipo)->value('id'),
        ]);
    }

    private function estado(DetallePlanEstudio $materia, CondicionCurricular $condicion): void
    {
        EstadoCurricular::factory()->create(['alumno_id' => $this->alumno->id, 'detalle_plan_estudio_id' => $materia->id, 'condicion' => $condicion]);
    }

    public function test_camino_feliz_preceptor(): void
    {
        $this->estado($this->matematica, CondicionCurricular::Regular);
        $acta = $this->acta($this->matematica);

        $this->get(route('panel.inscripciones-examen.create', [$this->alumno, 'materia' => $this->matematica->id]))->assertOk()
            ->assertSee('INSCRIPCION DE ALUMNO EN EXAMENES')->assertSee($acta->nro_acta_texto)->assertSee('Inscribir');

        $this->post(route('panel.inscripciones-examen.store', $this->alumno), ['acta_id' => $acta->id])
            ->assertRedirect(route('panel.inscripciones-examen.confirmacion'));

        $detalle = DetalleActa::where('alumno_id', $this->alumno->id)->sole();
        $this->assertSame(EstadoDetalleActa::Activo, $detalle->estado_detalle_acta);
        $this->assertNull($detalle->nota_acta);
        Mail::assertQueued(InscripcionExamenRegistrada::class);

        $this->get(route('panel.inscripciones-examen.confirmacion'))->assertOk()
            ->assertSee(InscripcionExamenService::CONFIRMACION)->assertSee('Descargar Comprobante');
        $this->get(route('panel.inscripciones-examen.create', [$this->alumno, 'materia' => $this->matematica->id]))
            ->assertSee('Dar de Baja')->assertSee('Compr.');

        $this->get(route('panel.inscripciones-examen.comprobante', $detalle))->assertOk()->assertHeader('content-type', 'application/pdf');
        auth()->logout();
        $this->get(route('validar.inscripcion-examen', $detalle->uuid))->assertOk()->assertSee($acta->nro_acta_texto);
    }

    public function test_cuotas_pendientes(): void
    {
        $this->estado($this->matematica, CondicionCurricular::Regular);
        $acta = $this->acta($this->matematica);
        Cuota::factory()->create(['carrera_id' => $this->alumno->carrera_id, 'mes_id' => Mes::where('descripcion', 'Marzo')->value('id'), 'anio' => now()->year]);
        $inscripcion = Cuota::factory()->create(['carrera_id' => $this->alumno->carrera_id, 'mes_id' => Mes::inscripcion()->id, 'anio' => now()->year]);
        DetallePagoCuota::factory()->create(['cuota_id' => $inscripcion->id, 'pago_id' => Pago::factory()->create(['alumno_id' => $this->alumno->id])->id]);
        $this->travelTo(CarbonImmutable::create((int) now()->year, 3, 20));

        $this->post(route('panel.inscripciones-examen.store', $this->alumno), ['acta_id' => $acta->id])
            ->assertRedirect(route('panel.alumnos.index'))
            ->assertSessionHas(AlumnoController::AVISO, InscripcionExamenService::MENSAJE_CUOTAS)
            ->assertSessionHas(AlumnoController::AVISO_LISTA, [$this->alumno->usuario->nombre_completo.' · Marzo '.now()->year]);
        $this->assertSame(0, DetalleActa::count());
    }

    public function test_no_cumple_condiciones_acta_regular(): void
    {
        $acta = $this->acta($this->analisis);

        $this->post(route('panel.inscripciones-examen.store', $this->alumno), ['acta_id' => $acta->id])
            ->assertRedirect(route('panel.alumnos.index'))
            ->assertSessionHas(AlumnoController::AVISO, InscripcionExamenService::MENSAJE_CONDICIONES)
            ->assertSessionHas(AlumnoController::AVISO_LISTA, [
                $this->analisis->materia->nombre_materia.': requiere Regular',
                $this->matematica->materia->nombre_materia.': requiere Aprobada',
            ]);
        $this->assertSame(0, DetalleActa::count());
    }

    public function test_acta_libre_sin_condiciones_pero_no_con_la_materia_aprobada(): void
    {
        $libre = $this->acta($this->analisis, TipoActa::LIBRE);
        $this->post(route('panel.inscripciones-examen.store', $this->alumno), ['acta_id' => $libre->id])
            ->assertRedirect(route('panel.inscripciones-examen.confirmacion'));

        $this->estado($this->matematica, CondicionCurricular::Aprobado);
        $this->post(route('panel.inscripciones-examen.store', $this->alumno), ['acta_id' => $this->acta($this->matematica, TipoActa::LIBRE)->id])
            ->assertSessionHas(AlumnoController::AVISO, InscripcionExamenService::MENSAJE_CONDICIONES);
        $this->assertSame(1, DetalleActa::count());
    }

    public function test_el_alumno_solo_inscribe_lo_suyo(): void
    {
        $this->estado($this->matematica, CondicionCurricular::Regular);
        $acta = $this->acta($this->matematica);
        $otro = Alumno::factory()->create(['estado_alumno' => EstadoAlumno::Inscripto, 'estado_legajo' => EstadoLegajo::Activo]);
        $ajeno = DetalleActa::factory()->create(['alumno_id' => $otro->id]);
        $this->actingAs(User::find($this->alumno->user_id));

        $this->get(route('panel.inscripciones-examen.create', $this->alumno))->assertOk();
        $this->get(route('panel.inscripciones-examen.create', $otro))->assertForbidden();
        $this->post(route('panel.inscripciones-examen.store', $otro), ['acta_id' => $acta->id])->assertForbidden();
        $this->get(route('panel.inscripciones-examen.comprobante', $ajeno))->assertForbidden();

        $this->post(route('panel.inscripciones-examen.store', $this->alumno), ['acta_id' => $acta->id])
            ->assertRedirect(route('panel.inscripciones-examen.confirmacion'));
    }
}
