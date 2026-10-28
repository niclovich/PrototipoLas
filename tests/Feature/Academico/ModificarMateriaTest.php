<?php

namespace Tests\Feature\Academico;

use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Models\DetallePlanEstudio;
use App\Models\Materia;
use App\Models\PlanEstudio;
use App\Models\User;
use App\Services\Academico\MateriaService;
use Illuminate\Validation\ValidationException;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

/** CU 2 Modificar datos de una Materia (D2: la vinculación se controla al tocar "Guardar Cambios"). */
class ModificarMateriaTest extends TestCase
{
    use ConRolesYPermisos;

    private User $coordinador;

    private Materia $materia;

    protected function setUp(): void
    {
        parent::setUp();
        $this->coordinador = $this->crearUsuario(RolSistema::Coordinador);
        $this->materia = Materia::factory()->create(['nombre_materia' => 'Matemática']);
    }

    private function modificar(array $datos, ?Materia $materia = null)
    {
        $materia ??= $this->materia;

        return $this->actingAs($this->coordinador)->from(route('panel.materias.edit', $materia))
            ->put(route('panel.materias.update', $materia), ['nombre_materia' => 'Matemática I', 'estado_materia' => 'Activo', ...$datos]);
    }

    private function vincular(EstadoGeneral $plan = EstadoGeneral::Activo, EstadoGeneral $detalle = EstadoGeneral::Activo): void
    {
        DetallePlanEstudio::factory()->create([
            'materia_id' => $this->materia->id,
            'plan_estudio_id' => PlanEstudio::factory()->create(['estado_plan_estudio' => $plan])->id,
            'estado_det_plan_estudio' => $detalle,
        ]);
    }

    public function test_muestra_datos_actuales(): void
    {
        $this->actingAs($this->coordinador)->get(route('panel.materias.edit', $this->materia))->assertOk()
            ->assertSeeInOrder(['GESTIÓN DE MATERIAS', 'Modificar Materia', 'Datos de la Materia', 'Nombre de la Materia', 'Estado', 'Cancelar', 'Guardar Cambios'])
            ->assertSee('value="Matemática"', false)
            ->assertDontSee('disabled', false);
    }

    public function test_modifica_nombre_y_estado(): void
    {
        $this->modificar(['estado_materia' => 'Inactivo'])->assertRedirect(route('panel.materias.confirmacion'));

        $this->materia->refresh();
        $this->assertSame('Matemática I', $this->materia->nombre_materia);
        $this->assertSame(EstadoGeneral::Inactivo, $this->materia->estado_materia);

        $this->followRedirects($this->modificar(['nombre_materia' => 'Matemática II']))
            ->assertSeeInOrder([MateriaService::CONFIRMACION_MODIFICACION, 'Materia: Matemática II', 'Estado: Activo', 'Aceptar']);
        $this->assertSame('Materia modificada exitosamente', MateriaService::CONFIRMACION_MODIFICACION);
    }

    public function test_vinculada_a_plan_activo_abre_el_formulario_pero_al_guardar_termina_el_cu(): void
    {
        $this->vincular();

        // D2: el formulario se abre.
        $this->actingAs($this->coordinador)->get(route('panel.materias.edit', $this->materia))->assertOk();

        // Al guardar: aviso en P1 y fin del CU, sin cambios.
        $this->modificar([])->assertRedirect(route('panel.materias.index'));
        $this->followRedirects($this->modificar([]))
            ->assertSee('GESTIÓN DE MATERIAS')
            ->assertSee(MateriaService::MENSAJE_MATERIA_VINCULADA)
            ->assertSee('data-abrir', false);
        $this->assertSame('No se puede modificar la materia porque ya se encuentra vinculada a un Plan de Estudio', MateriaService::MENSAJE_MATERIA_VINCULADA);
        $this->assertSame('Matemática', $this->materia->fresh()->nombre_materia);
    }

    public function test_plan_inactivo_o_detalle_dado_de_baja_no_bloquean(): void
    {
        $this->vincular(EstadoGeneral::Inactivo);
        $this->vincular(EstadoGeneral::Activo, EstadoGeneral::Inactivo);

        $this->modificar([])->assertRedirect(route('panel.materias.confirmacion'));
        $this->assertSame('Matemática I', $this->materia->fresh()->nombre_materia);
    }

    public function test_no_modifica_a_nombre_existente_y_puede_guardar_con_su_mismo_nombre(): void
    {
        Materia::factory()->create(['nombre_materia' => 'Física']);

        $this->modificar(['nombre_materia' => 'física'])->assertSessionHasErrors(['nombre_materia' => MateriaService::MENSAJE_MATERIA_EXISTENTE]);
        $this->followRedirects($this->modificar(['nombre_materia' => 'Física']))->assertSee('Materia ya existente');

        $this->modificar(['nombre_materia' => 'Matemática'])->assertSessionHasNoErrors();
    }

    public function test_estado_obligatorio_y_del_enum(): void
    {
        $this->modificar(['estado_materia' => 'Activa'])->assertSessionHasErrors(['estado_materia' => 'Debe seleccionar un estado.']);
    }

    public function test_acepta_150_y_rechaza_151_caracteres(): void
    {
        $this->modificar(['nombre_materia' => str_repeat('M', 150)])
            ->assertRedirect(route('panel.materias.confirmacion'));
        $this->modificar(['nombre_materia' => str_repeat('N', 151)])
            ->assertSessionHasErrors(['nombre_materia']);

        $this->assertSame(150, mb_strlen($this->materia->fresh()->nombre_materia));
    }

    public function test_traduce_la_colision_de_unicidad_al_modificar(): void
    {
        Materia::factory()->create(['nombre_materia' => 'Física']);

        try {
            app(MateriaService::class)->modificar($this->materia, 'Física', EstadoGeneral::Activo);
            $this->fail('La colisión de unicidad debía traducirse a un error de validación.');
        } catch (ValidationException $error) {
            $this->assertSame(
                [MateriaService::MENSAJE_MATERIA_EXISTENTE],
                $error->errors()['nombre_materia'] ?? [],
            );
        }

        $this->assertSame('Matemática', $this->materia->fresh()->nombre_materia);
    }

    public function test_cancelar_no_modifica_y_404(): void
    {
        $this->actingAs($this->coordinador)->get(route('panel.materias.edit', $this->materia))
            ->assertSee('href="'.route('panel.materias.index').'"', false);
        $this->assertSame('Matemática', $this->materia->fresh()->nombre_materia);

        $this->actingAs($this->coordinador)->get(route('panel.materias.edit', 999999))->assertNotFound();
    }
}
