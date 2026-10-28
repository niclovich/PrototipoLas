<?php

namespace Tests\Feature\Examenes;

use App\Enums\Asentada;
use App\Enums\EstadoActa;
use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Models\ActaExamen;
use App\Models\Carrera;
use App\Models\DetalleActa;
use App\Models\DetallePlanEstudio;
use App\Models\IntegranteTribunal;
use App\Models\Llamado;
use App\Models\Materia;
use App\Models\Modalidad;
use App\Models\PlanEstudio;
use App\Models\RolTribunal;
use App\Models\TipoActa;
use App\Models\User;
use App\Notifications\TribunalActaExamen;
use App\Services\Examenes\ActaExamenService;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

/** CU 21 Registrar y CU 22 Modificar datos de Acta de Examen. */
class ActaExamenTest extends TestCase
{
    use ConRolesYPermisos;

    private User $coordinador;

    private Carrera $carrera;

    private DetallePlanEstudio $detalle;

    /** @var list<User> */
    private array $docentes;

    /** @var list<int> */
    private array $roles;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->coordinador = $this->crearUsuario(RolSistema::Coordinador);
        $this->carrera = Carrera::factory()->create();
        $plan = PlanEstudio::factory()->create(['carrera_id' => $this->carrera->id]);
        $this->detalle = DetallePlanEstudio::factory()->create([
            'plan_estudio_id' => $plan->id,
            'materia_id' => Materia::factory()->create(['nombre_materia' => 'Análisis Matemático I'])->id,
        ]);
        Modalidad::factory()->create(['modalidad' => 'Presencial']);
        Llamado::factory()->create(['llamado' => 'S · 2° Llamado']);
        TipoActa::factory()->create(['descripcion' => 'Regular']);
        $this->roles = collect(['Presidente', 'Vocal 1', 'Vocal 2'])->map(fn ($d) => RolTribunal::factory()->create(['descripcion' => $d])->id)->all();
        $this->docentes = collect(['Gutierrez', 'Romero', 'Sosa'])
            ->map(fn ($apellido) => User::factory()->personal(RolSistema::Docente)->create(['apellido_usuario' => $apellido]))->all();
    }

    private function datos(array $cambios = []): array
    {
        return [
            'detalle_plan_estudio_id' => $this->detalle->id,
            'fecha_acta' => today()->addDays(10)->format('d/m/Y'),
            'modalidad_id' => Modalidad::where('modalidad', 'Presencial')->value('id'),
            'llamado_id' => Llamado::where('llamado', 'S · 2° Llamado')->value('id'),
            'tipo_acta_id' => TipoActa::where('descripcion', 'Regular')->value('id'),
            'tribunal' => [
                ['user_id' => $this->docentes[0]->id, 'rol_tribunal_id' => $this->roles[0]],
                ['user_id' => $this->docentes[1]->id, 'rol_tribunal_id' => $this->roles[1]],
            ],
            ...$cambios,
        ];
    }

    private function registrar(array $cambios = [])
    {
        return $this->actingAs($this->coordinador)->from(route('panel.actas.create', $this->carrera))
            ->post(route('panel.actas.store', $this->carrera), $this->datos($cambios));
    }

    public function test_p1_lista_las_actas_de_la_carrera_con_filtros(): void
    {
        ActaExamen::factory()->create(['detalle_plan_estudio_id' => $this->detalle->id, 'nro_acta' => 142]);
        ActaExamen::factory()->create(['detalle_plan_estudio_id' => $this->detalle->id, 'nro_acta' => 141, 'asentada' => Asentada::Si, 'estado_acta' => EstadoActa::Finalizado]);
        ActaExamen::factory()->create(['nro_acta' => 999]);

        $this->actingAs($this->coordinador)->get(route('panel.actas.index', ['carrera' => $this->carrera->id]))->assertOk()
            ->assertSeeInOrder(['GESTIÓN DE ACTAS EXAMEN', 'Seleccionar Carrera', 'Filtrar', 'Asentada', 'No/Si', 'Registrar Acta',
                'Nro Acta', 'Materia', 'Modalidad', 'Llamado', 'Tipo Acta', 'Fecha', 'Asentada', 'Acción']);

        $pedir = fn (array $filtros) => $this->actingAs($this->coordinador)
            ->post(route('panel.actas.datatable'), ['carrera' => $this->carrera->id, 'draw' => 1, ...$filtros])->json('data');
        $this->assertSame(['0142', '0141'], array_column($pedir([]), 'nro'));
        $this->assertSame(['0141'], array_column($pedir(['asentada' => 'S']), 'nro'));
        $this->assertSame(['Si'], array_column($pedir(['asentada' => 'S']), 'asentada_texto'));
        $this->assertSame(['0142'], array_column($pedir(['buscar' => '142']), 'nro'));
    }

    public function test_registra_el_acta_pendiente_con_numero_y_tribunal(): void
    {
        ActaExamen::factory()->create(['nro_acta' => 142]);

        $this->actingAs($this->coordinador)->get(route('panel.actas.create', $this->carrera))->assertOk()
            ->assertSeeInOrder(['Registrar Acta de Examen', 'Datos del Acta', 'Carrera', 'Materia', 'Fecha de Examen', 'Modalidad', 'Llamado',
                'Tipo de Acta', 'Tribunal Examinador', 'Docente', 'Buscar docente por apellido...', 'Rol en el Tribunal', '+ Agregar',
                'Docente', 'Rol', 'Acción', 'Cancelar', 'Registrar Acta']);

        $this->followRedirects($this->registrar(['estado_acta' => 'Finalizado']))
            ->assertSeeInOrder([ActaExamenService::CONFIRMACION_REGISTRO, ActaExamenService::CONFIRMACION_REGISTRO_TEXTO, 'Detalle del Registro',
                'Nro Acta: 0143 · Materia: Análisis Matemático I', 'Modalidad: Presencial · Llamado: S · 2° Llamado · Tipo: Regular',
                'Estado: Pendiente · Asentada: No', 'Tribunal: Gutierrez (Presidente), Romero (Vocal 1)', 'Aceptar']);

        $acta = ActaExamen::where('nro_acta', 143)->sole();
        $this->assertSame([EstadoActa::Pendiente, Asentada::No], [$acta->estado_acta, $acta->asentada]);
        $this->assertSame(2, IntegranteTribunal::where('tribunal_id', $acta->tribunal_id)->where('estado_integrante', 'Activo')->count());
        Notification::assertSentTo([$this->docentes[0], $this->docentes[1]], TribunalActaExamen::class);
    }

    public function test_cu21_alt_10_fecha_invalida(): void
    {
        $this->registrar(['fecha_acta' => today()->format('d/m/Y')])
            ->assertSessionHasErrors(['fecha_acta' => 'La fecha debe ser superior a la fecha actual.']);
        $this->followRedirects($this->registrar(['fecha_acta' => today()->format('d/m/Y')]))->assertSee('Fecha inválida');
    }

    public function test_tribunal_vacio_o_repetido_no_registra(): void
    {
        $this->registrar(['tribunal' => []])->assertSessionHasErrors('tribunal');
        $this->registrar(['tribunal' => [
            ['user_id' => $this->docentes[0]->id, 'rol_tribunal_id' => $this->roles[0]],
            ['user_id' => $this->docentes[0]->id, 'rol_tribunal_id' => $this->roles[1]],
        ]])->assertSessionHasErrors('tribunal');
        $this->assertSame(0, ActaExamen::count());
    }

    private function actaPendiente(): ActaExamen
    {
        $this->registrar();

        return ActaExamen::sole();
    }

    public function test_modifica_datos_y_tribunal(): void
    {
        $acta = $this->actaPendiente();
        Notification::fake();

        $this->actingAs($this->coordinador)->get(route('panel.actas.edit', $acta))->assertOk()
            ->assertSeeInOrder(['Modificar Acta de Examen', 'Datos del Acta · Nro Acta 0001', 'Análisis Matemático I', 'Tribunal Examinador',
                'Seleccione un docente de la materia', 'Quitar', 'Gutierrez', 'Romero', 'Guardar Cambios']);

        $nuevaFecha = today()->addDays(20);
        $this->followRedirects($this->actingAs($this->coordinador)->put(route('panel.actas.update', $acta), $this->datos([
            'fecha_acta' => $nuevaFecha->format('d/m/Y'),
            'tribunal' => [
                ['user_id' => $this->docentes[0]->id, 'rol_tribunal_id' => $this->roles[0]],
                ['user_id' => $this->docentes[2]->id, 'rol_tribunal_id' => $this->roles[2]],
            ],
        ])))->assertSee(ActaExamenService::CONFIRMACION_MODIFICACION);

        $acta->refresh();
        $this->assertSame($nuevaFecha->toDateString(), $acta->fecha_acta->toDateString());
        $this->assertSame(EstadoActa::Pendiente, $acta->estado_acta);
        $activos = IntegranteTribunal::where('tribunal_id', $acta->tribunal_id)->where('estado_integrante', EstadoGeneral::Activo->value)->pluck('user_id')->sort()->values()->all();
        $this->assertSame(collect([$this->docentes[0]->id, $this->docentes[2]->id])->sort()->values()->all(), $activos);
        $this->assertSame(EstadoGeneral::Inactivo, IntegranteTribunal::where('user_id', $this->docentes[1]->id)->sole()->estado_integrante);
        Notification::assertSentTo($this->docentes[1], TribunalActaExamen::class);
        Notification::assertSentTo($this->docentes[2], TribunalActaExamen::class);
    }

    public function test_cu22_alt_8_acta_con_inscriptos(): void
    {
        $acta = $this->actaPendiente();
        DetalleActa::factory()->create(['acta_examen_id' => $acta->id]);

        $this->followRedirects($this->actingAs($this->coordinador)->get(route('panel.actas.edit', $acta)))
            ->assertSee('El Acta de Examen no puede modificarse porque posee Alumnos inscriptos');
        $this->actingAs($this->coordinador)->put(route('panel.actas.update', $acta), $this->datos())
            ->assertSessionHas('aviso_acta', 'El Acta de Examen no puede modificarse porque posee Alumnos inscriptos');
    }

    public function test_cu22_alt_12_fecha_invalida(): void
    {
        $acta = $this->actaPendiente();
        $this->actingAs($this->coordinador)->from(route('panel.actas.edit', $acta))
            ->put(route('panel.actas.update', $acta), $this->datos(['fecha_acta' => today()->subDay()->format('d/m/Y')]))
            ->assertSessionHasErrors(['fecha_acta' => 'La fecha debe ser superior a la fecha actual.']);
    }
}
