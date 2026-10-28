<?php

namespace Tests\Feature\Examenes;

use App\Enums\Asentada;
use App\Enums\CondicionCurricular;
use App\Enums\CondicionExamen;
use App\Enums\EstadoActa;
use App\Enums\EstadoAlumno;
use App\Enums\EstadoLegajo;
use App\Enums\EstadoTramite;
use App\Enums\RolSistema;
use App\Mail\ResultadoExamen;
use App\Models\ActaExamen;
use App\Models\Alumno;
use App\Models\DetalleActa;
use App\Models\DetallePlanEstudio;
use App\Models\EstadoCurricular;
use App\Models\TituloTramite;
use App\Services\Examenes\AsentamientoActaService;
use Database\Seeders\CatalogosSeeder;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

/** CU6 Asentamiento de Acta: P1 Gestión de Actas → P2 Alumnos Inscriptos → P3 Registrar Nota → P4 Confirmación. */
class AsentamientoActaTest extends TestCase
{
    use ConRolesYPermisos;

    private ActaExamen $acta;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CatalogosSeeder::class);
        Mail::fake();
        $this->acta = ActaExamen::factory()->create(['nro_acta' => 142]);
        $this->actingAs($this->crearUsuario(RolSistema::Preceptor));
    }

    private function inscripto(array $alumno = []): DetalleActa
    {
        $plan = $this->acta->detallePlan->plan_estudio_id;
        $alumno = Alumno::factory()->create([
            'plan_estudio_id' => $plan, 'estado_alumno' => EstadoAlumno::Inscripto, 'estado_legajo' => EstadoLegajo::Activo, ...$alumno,
        ]);

        return DetalleActa::factory()->create(['acta_examen_id' => $this->acta->id, 'alumno_id' => $alumno->id]);
    }

    public function test_camino_feliz(): void
    {
        $aprobado = $this->inscripto(['nro_legajo' => 12458]);
        $desaprobado = $this->inscripto(['nro_legajo' => 12622]);
        // Otra materia del plan sin aprobar: nadie completa el plan.
        DetallePlanEstudio::factory()->create(['plan_estudio_id' => $this->acta->detallePlan->plan_estudio_id]);

        $this->get(route('panel.asentamiento.show', $this->acta))->assertOk()
            ->assertSee('ASENTAMIENTO DE ACTA DE EXAMEN')->assertSee(AsentamientoActaService::SIN_REGISTRAR);
        $this->get(route('panel.asentamiento.edit', $aprobado))->assertOk()->assertSee('1 · Nota, Observacion y Condicion');

        $this->put(route('panel.asentamiento.update', $aprobado), ['nota_acta' => '8'])->assertRedirect(route('panel.asentamiento.show', $this->acta));
        $this->put(route('panel.asentamiento.update', $desaprobado), ['nota_acta' => '3', 'observacion' => 'Se ausento a la segunda parte']);
        $this->assertSame(CondicionExamen::Aprobado, $aprobado->refresh()->condicion_final);
        $this->assertSame(CondicionExamen::Desaprobado, $desaprobado->refresh()->condicion_final);

        $this->post(route('panel.asentamiento.store', $this->acta), ['nro_folio' => 118])
            ->assertRedirect(route('panel.asentamiento.confirmacion'));

        $acta = $this->acta->refresh();
        $this->assertSame(EstadoActa::Finalizado, $acta->estado_acta);
        $this->assertSame(Asentada::Si, $acta->asentada);
        $this->assertSame(118, $acta->nro_folio);
        $estados = EstadoCurricular::where('nro_acta', 142)->orderBy('id')->get();
        $this->assertSame([CondicionCurricular::Aprobado, CondicionCurricular::Desaprobado], $estados->pluck('condicion')->all());
        $this->assertSame(0, TituloTramite::count());
        Mail::assertQueued(ResultadoExamen::class, 2);

        $this->get(route('panel.asentamiento.confirmacion'))->assertOk()
            ->assertSee(AsentamientoActaService::CONFIRMACION)
            ->assertSee('Nro Acta: 0142 · Nro Folio: 118')
            ->assertSee('Alumnos aprobados: 1 · Desaprobados: 1 · Ausentes: 0');
    }

    public function test_nota_fuera_de_rango(): void
    {
        $detalle = $this->inscripto();

        foreach (['11', '0', 'ocho', ''] as $nota) {
            $this->from(route('panel.asentamiento.edit', $detalle))
                ->put(route('panel.asentamiento.update', $detalle), ['nota_acta' => $nota])
                ->assertRedirect(route('panel.asentamiento.edit', $detalle))
                ->assertSessionHasErrors(['nota_acta' => AsentamientoActaService::MENSAJE_NOTA]);
        }
        $this->assertNull($detalle->refresh()->nota_acta);
    }

    public function test_numero_de_folio_invalido(): void
    {
        $this->inscripto()->update(['nota_acta' => 7, 'condicion_final' => CondicionExamen::Aprobado]);
        ActaExamen::factory()->create(['nro_folio' => 118, 'estado_acta' => EstadoActa::Finalizado, 'asentada' => Asentada::Si]);

        foreach (['118', '0', 'abc', ''] as $folio) {
            $this->post(route('panel.asentamiento.store', $this->acta), ['nro_folio' => $folio])
                ->assertSessionHasErrors(['nro_folio' => AsentamientoActaService::MENSAJE_FOLIO]);
        }
        $this->assertSame(EstadoActa::Pendiente, $this->acta->refresh()->estado_acta);
    }

    public function test_plan_completo_genera_tramite_de_titulo(): void
    {
        $detalle = $this->inscripto();
        $otra = DetallePlanEstudio::factory()->create(['plan_estudio_id' => $this->acta->detallePlan->plan_estudio_id]);
        EstadoCurricular::factory()->create(['alumno_id' => $detalle->alumno_id, 'detalle_plan_estudio_id' => $otra->id, 'condicion' => CondicionCurricular::Aprobado]);

        $this->put(route('panel.asentamiento.update', $detalle), ['nota_acta' => '7,50']);
        $this->post(route('panel.asentamiento.store', $this->acta), ['nro_folio' => 5]);

        $tramite = TituloTramite::where('alumno_id', $detalle->alumno_id)->sole();
        $this->assertSame(EstadoTramite::SinGestion, $tramite->estado_tramite);
        $this->assertNull($tramite->tipo_gestion_titulo_id);
        $this->assertSame(EstadoAlumno::TituloEnTramite, $detalle->alumno->refresh()->estado_alumno);
    }
}
