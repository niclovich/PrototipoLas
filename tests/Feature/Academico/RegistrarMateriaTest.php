<?php

namespace Tests\Feature\Academico;

use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Models\Materia;
use App\Models\User;
use App\Services\Academico\MateriaService;
use Illuminate\Validation\ValidationException;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

/** CU 1 Registrar datos de una nueva Materia. */
class RegistrarMateriaTest extends TestCase
{
    use ConRolesYPermisos;

    private User $coordinador;

    protected function setUp(): void
    {
        parent::setUp();
        $this->coordinador = $this->crearUsuario(RolSistema::Coordinador);
    }

    private function registrar(array $datos)
    {
        return $this->actingAs($this->coordinador)->from(route('panel.materias.create'))->post(route('panel.materias.store'), $datos);
    }

    public function test_coordinador_ve_el_formulario(): void
    {
        $this->get(route('panel.materias.create'))->assertRedirect(route('login'));
        $this->actingAs($this->crearUsuario(RolSistema::Preceptor))->get(route('panel.materias.create'))->assertForbidden();

        $this->actingAs($this->coordinador)->get(route('panel.materias.create'))->assertOk()
            ->assertSeeInOrder(['GESTIÓN DE MATERIAS', 'Registrar Materia', 'Datos de la Materia', 'Nombre de la Materia',
                'Ej: Matemática', 'Estado', 'Cancelar', 'Registrar Materia']);
    }

    public function test_el_estado_se_muestra_activo_y_deshabilitado(): void
    {
        $html = $this->actingAs($this->coordinador)->get(route('panel.materias.create'))->getContent();

        $this->assertMatchesRegularExpression('/<select name="estado_materia"[^>]*disabled/s', $html);
        $this->assertStringContainsString('<option value="Activo" selected', $html);
    }

    public function test_registra_materia_activo(): void
    {
        $this->registrar(['nombre_materia' => '  Programación   I ', 'estado_materia' => 'Inactivo'])
            ->assertRedirect(route('panel.materias.confirmacion'));

        $materia = Materia::sole();
        $this->assertSame('Programación I', $materia->nombre_materia);
        $this->assertSame(EstadoGeneral::Activo, $materia->estado_materia, 'El servidor registra siempre Activo (D1).');

        $this->followRedirects($this->registrar(['nombre_materia' => 'Matemática']))
            ->assertSeeInOrder([MateriaService::CONFIRMACION_REGISTRO, 'Materia: Matemática', 'Estado: Activo', 'Aceptar']);
        $this->assertSame('Materia registrada exitosamente', MateriaService::CONFIRMACION_REGISTRO);
    }

    public function test_confirmacion_sin_datos_redirige_a_p1(): void
    {
        $this->actingAs($this->coordinador)->get(route('panel.materias.confirmacion'))->assertRedirect(route('panel.materias.index'));
    }

    public function test_no_registra_materia_existente(): void
    {
        Materia::factory()->inactiva()->create(['nombre_materia' => 'Matemática']);

        $this->registrar(['nombre_materia' => ' matemática '])->assertSessionHasErrors(['nombre_materia' => MateriaService::MENSAJE_MATERIA_EXISTENTE]);
        $this->assertSame('Ya existe una materia con ese nombre', MateriaService::MENSAJE_MATERIA_EXISTENTE);
        $this->followRedirects($this->registrar(['nombre_materia' => 'Matemática']))
            ->assertSee('Materia ya existente')->assertSee('data-foco="#nombre_materia"', false);
        $this->assertSame(1, Materia::count());
    }

    public function test_nombre_obligatorio(): void
    {
        $this->registrar(['nombre_materia' => ''])->assertSessionHasErrors(['nombre_materia' => 'Debe ingresar el nombre de la materia.']);
    }

    public function test_acepta_150_y_rechaza_151_caracteres(): void
    {
        $this->registrar(['nombre_materia' => str_repeat('M', 150)])
            ->assertRedirect(route('panel.materias.confirmacion'));
        $this->registrar(['nombre_materia' => str_repeat('N', 151)])
            ->assertSessionHasErrors(['nombre_materia']);

        $this->assertSame(150, mb_strlen(Materia::sole()->nombre_materia));
    }

    public function test_traduce_la_colision_de_unicidad_al_registrar(): void
    {
        Materia::factory()->create(['nombre_materia' => 'Matemática']);

        try {
            app(MateriaService::class)->registrar('Matemática');
            $this->fail('La colisión de unicidad debía traducirse a un error de validación.');
        } catch (ValidationException $error) {
            $this->assertSame(
                [MateriaService::MENSAJE_MATERIA_EXISTENTE],
                $error->errors()['nombre_materia'] ?? [],
            );
        }

        $this->assertSame(1, Materia::count());
    }
}
