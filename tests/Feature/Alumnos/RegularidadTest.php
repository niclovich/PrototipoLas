<?php

namespace Tests\Feature\Alumnos;

use App\Enums\CondicionCurricular;
use App\Enums\CondicionCursada;
use App\Enums\EstadoAlumno;
use App\Enums\EstadoLegajo;
use App\Enums\RolSistema;
use App\Mail\CondicionRegistrada;
use App\Models\Alumno;
use App\Models\AlumnoComision;
use App\Models\Comision;
use App\Models\DetComision;
use App\Models\EstadoCurricular;
use App\Services\Alumnos\RegularidadService;
use Database\Seeders\CatalogosSeeder;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

/** CU4 Registro de Regularidad Académica por Materia (P1 → P2 → P3 → P4 → P5), actor Docente o Preceptor. */
class RegularidadTest extends TestCase
{
    use ConRolesYPermisos;

    private AlumnoComision $inscripcion;

    private Comision $comision;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CatalogosSeeder::class);
        Mail::fake();
        $alumno = Alumno::factory()->create([
            'nro_legajo' => 12458, 'estado_alumno' => EstadoAlumno::Inscripto, 'estado_legajo' => EstadoLegajo::Activo,
        ]);
        $this->comision = Comision::factory()->create(['carrera_id' => $alumno->carrera_id, 'codigo_comision' => 'COM-2026-1A']);
        $det = DetComision::factory()->create(['comision_id' => $this->comision->id]);
        $this->inscripcion = AlumnoComision::factory()->create(['alumno_id' => $alumno->id, 'det_comision_id' => $det->id]);
        $alumno->carrera->update(['estado_carrera' => 'Activo']);
    }

    public function test_camino_feliz_docente_ve_todas_las_comisiones(): void
    {
        $this->actingAs($this->crearUsuario(RolSistema::Docente));

        $this->get(route('panel.regularidad.index', ['carrera_id' => $this->comision->carrera_id]))->assertOk()->assertSee('COM-2026-1A');
        $this->get(route('panel.regularidad.materias', $this->comision))->assertOk()->assertSee('Comision: COM-2026-1A');
        $this->get(route('panel.regularidad.alumnos', $this->inscripcion->det_comision_id))->assertOk()
            ->assertSee('12458')->assertSee(CondicionCursada::EN_CURSO);
        $this->get(route('panel.regularidad.edit', $this->inscripcion))->assertOk()->assertSee('Registrar Regularidad');

        $this->put(route('panel.regularidad.update', $this->inscripcion), [
            'nota_p1' => '8', 'nota_p2' => '7,5', 'nota_r1' => '', 'nota_r2' => '', 'faltas_alumno' => '2', 'condicion_final' => 'Regular',
        ])->assertRedirect(route('panel.regularidad.confirmacion'));

        $this->inscripcion->refresh();
        $this->assertSame(CondicionCursada::Regular, $this->inscripcion->condicion_final);
        $this->assertSame('7.50', $this->inscripcion->nota_p2);
        $this->assertSame(2, $this->inscripcion->faltas_alumno);
        $estado = EstadoCurricular::where('alumno_id', $this->inscripcion->alumno_id)->sole();
        $this->assertSame(CondicionCurricular::Regular, $estado->condicion);
        $this->assertSame('7.75', $estado->nota_condicion);
        Mail::assertQueued(CondicionRegistrada::class);

        $this->get(route('panel.regularidad.confirmacion'))->assertOk()
            ->assertSee(RegularidadService::CONFIRMACION)->assertSee('Nota P1: 8 · Nota P2: 7,5 · Faltas: 2')->assertSee('Condicion Final: Regular');
    }

    public function test_cada_registro_crea_un_nuevo_estado_curricular(): void
    {
        $this->actingAs($this->crearUsuario(RolSistema::Preceptor));
        $datos = ['nota_p1' => '2', 'faltas_alumno' => '9', 'condicion_final' => 'Libre'];

        $this->put(route('panel.regularidad.update', $this->inscripcion), $datos)->assertRedirect();
        $this->put(route('panel.regularidad.update', $this->inscripcion), [...$datos, 'nota_r1' => '6', 'condicion_final' => 'Regular'])->assertRedirect();

        $this->assertSame(['Libre', 'Regular'], EstadoCurricular::orderBy('id')->pluck('condicion')->map->value->all());
    }

    public function test_nota_con_formato_invalido(): void
    {
        $this->actingAs($this->crearUsuario(RolSistema::Preceptor));

        $this->from(route('panel.regularidad.edit', $this->inscripcion))
            ->put(route('panel.regularidad.update', $this->inscripcion), ['nota_p1' => '11', 'condicion_final' => 'Regular'])
            ->assertRedirect(route('panel.regularidad.edit', $this->inscripcion))
            ->assertSessionHasErrors(['nota_p1' => RegularidadService::MENSAJE_FORMATO_NOTA]);

        $this->assertSame(0, EstadoCurricular::count());
        $this->assertNull($this->inscripcion->refresh()->condicion_final);
    }
}
