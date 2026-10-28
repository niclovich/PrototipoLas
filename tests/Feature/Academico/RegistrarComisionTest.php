<?php

namespace Tests\Feature\Academico;

use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Models\Carrera;
use App\Models\Comision;
use App\Models\DetallePlanEstudio;
use App\Models\Duracion;
use App\Models\Modalidad;
use App\Models\PlanEstudio;
use App\Models\User;
use App\Services\Academico\ComisionService;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

/** CU 17 Registrar datos de una nueva Comisión. */
class RegistrarComisionTest extends TestCase
{
    use ConRolesYPermisos;

    private User $coordinador;

    private Carrera $carrera;

    private Modalidad $modalidad;

    protected function setUp(): void
    {
        parent::setUp();
        $this->coordinador = $this->crearUsuario(RolSistema::Coordinador);
        $this->modalidad = Modalidad::factory()->create(['modalidad' => 'Presencial']);
        $this->carrera = Carrera::factory()->create([
            'nombre_carrera' => 'Licenciatura en Análisis de Sistemas',
            'duracion_id' => Duracion::factory()->create(['cantidad_anios' => 3])->id,
        ]);
        $plan = PlanEstudio::factory()->create(['carrera_id' => $this->carrera->id]);
        foreach ([[1, 96], [1, 64], [2, 128]] as [$anio, $carga]) {
            DetallePlanEstudio::factory()->create(['plan_estudio_id' => $plan->id, 'anio_curso_det_plan_estudio' => $anio, 'carga_horaria_det_plan_estudio' => $carga]);
        }
        DetallePlanEstudio::factory()->inactivo()->create(['plan_estudio_id' => $plan->id, 'anio_curso_det_plan_estudio' => 1]);
    }

    private function registrar(array $cambios = [])
    {
        return $this->actingAs($this->coordinador)->from(route('panel.comisiones.create', $this->carrera))
            ->post(route('panel.comisiones.store', $this->carrera), [
                'codigo_comision' => 'A',
                'modalidad_id' => $this->modalidad->id,
                'capacidad_comision' => 40,
                'anio_curso' => 1,
                'anio_lectivo' => (int) now()->format('Y'),
                'horario_comision' => 'Lu-Mi-Vi 8 a 12 hs',
                ...$cambios,
            ]);
    }

    public function test_p1_lista_las_comisiones_de_la_carrera_elegida(): void
    {
        Comision::factory()->create(['carrera_id' => $this->carrera->id, 'codigo_comision' => 'COM-01']);
        Comision::factory()->create(['codigo_comision' => 'OTRA-99']);

        $this->actingAs($this->coordinador)->get(route('panel.comisiones.index', ['carrera' => $this->carrera->id]))->assertOk()
            ->assertSeeInOrder(['GESTIÓN DE COMISIONES', 'Seleccionar Carrera', 'Licenciatura en Análisis de Sistemas', 'Registrar Comisión',
                'CÓDIGO', 'AÑO CURSO', 'AÑO LECTIVO', 'HORARIO', 'CAPACIDAD', 'MODALIDAD', 'ESTADO', 'ACCIONES']);

        $json = $this->actingAs($this->coordinador)->post(route('panel.comisiones.datatable'), ['carrera' => $this->carrera->id, 'draw' => 1])->json('data');
        $this->assertSame(['COM-01'], array_column($json, 'codigo_comision'));
    }

    public function test_registra_la_comision_con_sus_materias_y_carga_calculada(): void
    {
        $this->actingAs($this->coordinador)->get(route('panel.comisiones.create', $this->carrera))->assertOk()
            ->assertSeeInOrder(['Registrar Comisión', '1 · Datos de la Comisión', 'Carrera', 'Código de Comisión', 'Modalidad', 'Capacidad',
                'Año de Curso', 'Año Lectivo', 'Carga Horaria (calculada)', 'Horario', 'Registrar Comisión', 'Cancelar']);

        $this->followRedirects($this->registrar(['carga_horaria_comision' => 1]))
            ->assertSeeInOrder([ComisionService::CONFIRMACION_REGISTRO, 'Carrera: Licenciatura en Análisis de Sistemas',
                'Carga Horaria: 160 hs · Materias: 2', 'Aceptar']);

        $comision = Comision::sole();
        $this->assertSame(EstadoGeneral::Activo, $comision->estado_comision);
        $this->assertSame(160, $comision->carga_horaria_comision);
        $this->assertSame($this->modalidad->id, $comision->modalidad_id);
        $this->assertSame(2, $comision->detalles()->where('estado_det_comision', 'Activo')->count());
    }

    public function test_alt_10_anio_de_curso_invalido(): void
    {
        $this->registrar(['anio_curso' => 4])->assertSessionHasErrors(['anio_curso' => 'El Año de Curso debe estar entre 1 y la duración de la Carrera']);
        $this->followRedirects($this->registrar(['anio_curso' => 4]))->assertSee('Año de Curso inválido');
    }

    public function test_alt_13_anio_lectivo_anterior(): void
    {
        $this->registrar(['anio_lectivo' => (int) now()->format('Y') - 1])
            ->assertSessionHasErrors(['anio_lectivo' => 'El Año Lectivo no puede ser anterior al Año Actual']);
    }

    public function test_alt_16_codigo_existente(): void
    {
        $this->registrar();
        $this->registrar()->assertSessionHasErrors(['codigo_comision' => 'Ya existe una Comisión con ese Código para la Carrera, Año de Curso y Año Lectivo seleccionados']);
        $this->registrar(['anio_curso' => 2])->assertSessionHasNoErrors();
    }

    public function test_alt_19_capacidad_invalida(): void
    {
        $this->registrar(['capacidad_comision' => 0])->assertSessionHasErrors(['capacidad_comision' => 'La capacidad debe ser un número mayor a cero']);
    }

    public function test_sin_materias_en_el_anio_no_registra(): void
    {
        $this->registrar(['anio_curso' => 3])->assertSessionHasErrors(['anio_curso' => ComisionService::MENSAJE_SIN_MATERIAS]);
        $this->assertSame(0, Comision::count());
    }

    public function test_carrera_no_activa_da_404(): void
    {
        $pendiente = Carrera::factory()->pendiente()->create();
        $this->actingAs($this->coordinador)->get(route('panel.comisiones.create', $pendiente))->assertNotFound();
    }
}
