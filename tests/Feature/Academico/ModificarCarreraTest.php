<?php

namespace Tests\Feature\Academico;

use App\Enums\EstadoCarrera;
use App\Enums\RolSistema;
use App\Models\Carrera;
use App\Models\Duracion;
use App\Models\Modalidad;
use App\Models\PlanEstudio;
use App\Models\User;
use App\Rules\ResolucionMinisterial;
use App\Services\Academico\CarreraService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Validation\ValidationException;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

/** CU 4 Modificar datos de una Carrera. */
class ModificarCarreraTest extends TestCase
{
    use ConRolesYPermisos;

    private User $coordinador;

    private Carrera $carrera;

    protected function setUp(): void
    {
        parent::setUp();
        $this->coordinador = $this->crearUsuario(RolSistema::Coordinador);
        $this->carrera = Carrera::factory()->pendiente()->create([
            'nombre_carrera' => 'Licenciatura en Analisis de Sistemas', 'resolucion_carrera' => '1234/2020',
            'modalidad_id' => Modalidad::factory()->create(['modalidad' => 'Presencial'])->id,
            'duracion_id' => Duracion::factory()->create(['duracion' => '5 años', 'cantidad_anios' => 5])->id,
        ]);
    }

    private function datos(array $cambios = []): array
    {
        return [
            'nombre_carrera' => $this->carrera->nombre_carrera,
            'modalidad_id' => $this->carrera->modalidad_id,
            'duracion_id' => $this->carrera->duracion_id,
            'resolucion_carrera' => 'Resolución N° 1234/2020',
            'estado_carrera' => EstadoCarrera::Activo->value,
            ...$cambios,
        ];
    }

    private function modificar(array $cambios = [], ?Carrera $carrera = null)
    {
        $carrera ??= $this->carrera;

        return $this->actingAs($this->coordinador)->from(route('panel.carreras.edit', $carrera))
            ->put(route('panel.carreras.update', $carrera), $this->datos($cambios));
    }

    public function test_muestra_datos_actuales(): void
    {
        $this->actingAs($this->coordinador)->get(route('panel.carreras.edit', $this->carrera))->assertOk()
            ->assertSeeInOrder(['GESTIÓN DE CARRERAS', 'Modificar Carrera', 'Datos de la Carrera', 'Nombre de la Carrera', 'Modalidad',
                'Duración', 'Resolución Ministerial', 'Estado', 'Cancelar', 'Guardar Cambios'])
            ->assertSee('value="Licenciatura en Analisis de Sistemas"', false)
            ->assertSee('value="Resolución N° 1234/2020"', false)
            ->assertDontSee('disabled', false);
    }

    public function test_modifica_carrera_sin_plan(): void
    {
        $modalidad = Modalidad::factory()->create(['modalidad' => 'A distancia']);

        $this->modificar(['nombre_carrera' => 'Licenciatura en Sistemas', 'modalidad_id' => $modalidad->id, 'resolucion_carrera' => '999/2024'])
            ->assertRedirect(route('panel.carreras.confirmacion'));

        $this->carrera->refresh();
        $this->assertSame('Licenciatura en Sistemas', $this->carrera->nombre_carrera);
        $this->assertSame($modalidad->id, $this->carrera->modalidad_id);
        $this->assertSame('999/2024', $this->carrera->resolucion_carrera);
        $this->assertSame(EstadoCarrera::Activo, $this->carrera->estado_carrera);

        $this->followRedirects($this->modificar(['nombre_carrera' => 'Licenciatura en Sistemas', 'estado_carrera' => 'Inactivo']))
            ->assertSeeInOrder([CarreraService::CONFIRMACION_MODIFICACION, 'Carrera: Licenciatura en Sistemas', 'Estado: Inactivo', 'Aceptar']);
        $this->assertSame('Carrera modificada exitosamente', CarreraService::CONFIRMACION_MODIFICACION);
    }

    public function test_con_plan_activo_solo_habilita_estado(): void
    {
        PlanEstudio::factory()->activo()->create(['carrera_id' => $this->carrera->id]);

        $html = $this->actingAs($this->coordinador)->get(route('panel.carreras.edit', $this->carrera))->assertOk()->getContent();
        $this->assertSame(4, substr_count($html, 'disabled="disabled"'), 'Nombre, Modalidad, Duración y Resolución deshabilitados.');
        $this->assertStringContainsString(CarreraService::AVISO_PLAN_ACTIVO, $html);

        // El servidor ignora los cambios estructurales aunque lleguen.
        $this->actingAs($this->coordinador)->put(route('panel.carreras.update', $this->carrera), [
            'nombre_carrera' => 'Otro nombre', 'resolucion_carrera' => 'basura', 'estado_carrera' => 'Activo',
        ])->assertSessionHasNoErrors();

        $this->carrera->refresh();
        $this->assertSame('Licenciatura en Analisis de Sistemas', $this->carrera->nombre_carrera);
        $this->assertSame('1234/2020', $this->carrera->resolucion_carrera);
        $this->assertSame(EstadoCarrera::Activo, $this->carrera->estado_carrera);
    }

    public function test_plan_inactivo_no_bloquea(): void
    {
        PlanEstudio::factory()->inactivo()->create(['carrera_id' => $this->carrera->id]);

        $this->modificar(['nombre_carrera' => 'Nuevo nombre'])->assertSessionHasNoErrors();
        $this->assertSame('Nuevo nombre', $this->carrera->fresh()->nombre_carrera);
    }

    public function test_conserva_catalogos_actuales_eliminados_pero_rechaza_otros_eliminados(): void
    {
        $this->carrera->modalidad->delete();
        $this->carrera->duracion->delete();

        $this->actingAs($this->coordinador)->get(route('panel.carreras.edit', $this->carrera))
            ->assertOk()
            ->assertSee('Presencial')
            ->assertSee('5 años');
        $this->modificar()->assertSessionHasNoErrors();

        $otraModalidad = Modalidad::factory()->create(['modalidad' => 'Eliminada']);
        $otraDuracion = Duracion::factory()->create(['duracion' => '9 años', 'cantidad_anios' => 9]);
        $otraModalidad->delete();
        $otraDuracion->delete();

        $this->modificar(['modalidad_id' => $otraModalidad->id, 'duracion_id' => $otraDuracion->id])
            ->assertSessionHasErrors(['modalidad_id', 'duracion_id']);
    }

    public function test_no_modifica_a_nombre_existente_y_puede_guardar_con_su_mismo_nombre(): void
    {
        Carrera::factory()->create(['nombre_carrera' => 'Tecnicatura en Programacion']);

        $this->modificar(['nombre_carrera' => 'tecnicatura en programacion'])
            ->assertSessionHasErrors(['nombre_carrera' => CarreraService::MENSAJE_CARRERA_EXISTENTE]);
        $this->followRedirects($this->modificar(['nombre_carrera' => 'Tecnicatura en Programacion']))->assertSee('Carrera ya existente');

        $this->modificar()->assertSessionHasNoErrors();
    }

    public function test_traduce_la_unicidad_concurrente_al_modificar(): void
    {
        Carrera::updating(function () {
            throw new UniqueConstraintViolationException(
                'mysql',
                'update `carreras` set `nombre_carrera` = ?',
                [],
                new \Exception("SQLSTATE[23000]: Duplicate entry 'x' for key 'carreras_nombre_carrera_unique'"),
            );
        });

        try {
            app(CarreraService::class)->modificar($this->carrera, $this->datos(['nombre_carrera' => 'Nombre tomado']));
            $this->fail('Debía traducir la colisión concurrente.');
        } catch (ValidationException $error) {
            $this->assertSame(['nombre_carrera' => [CarreraService::MENSAJE_CARRERA_EXISTENTE]], $error->errors());
        }

        $this->assertSame('Licenciatura en Analisis de Sistemas', $this->carrera->fresh()->nombre_carrera);
    }

    public function test_resolucion_mal_formada_al_modificar_y_no_valida_si_no_cambio(): void
    {
        $this->modificar(['resolucion_carrera' => '12-2020'])->assertSessionHasErrors(['resolucion_carrera' => ResolucionMinisterial::MENSAJE]);

        // Una carrera cargada antes con otro formato se puede guardar si la resolución no cambia.
        $vieja = Carrera::factory()->create(['resolucion_carrera' => 'RM-77']);
        $this->modificar([
            'nombre_carrera' => $vieja->nombre_carrera, 'modalidad_id' => $vieja->modalidad_id, 'duracion_id' => $vieja->duracion_id,
            'resolucion_carrera' => 'RM-77',
        ], $vieja)->assertSessionHasNoErrors();
    }

    public function test_estado_obligatorio_y_del_enum(): void
    {
        $this->modificar(['estado_carrera' => 'Activa'])->assertSessionHasErrors(['estado_carrera' => 'Debe seleccionar un estado.']);
    }

    public function test_cancelar_no_modifica_y_404(): void
    {
        $antes = $this->carrera->fresh()->toArray();
        $this->actingAs($this->coordinador)->get(route('panel.carreras.edit', $this->carrera))
            ->assertSee('href="'.route('panel.carreras.index').'"', false);
        $this->assertSame($antes, $this->carrera->fresh()->toArray());

        $this->actingAs($this->coordinador)->get(route('panel.carreras.edit', 999999))->assertNotFound();
    }
}
