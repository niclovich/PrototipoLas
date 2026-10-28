<?php

namespace Tests\Feature\Academico;

use App\Enums\RolSistema;
use App\Http\Controllers\Panel\Academico\MateriaController;
use App\Models\Materia;
use App\Models\User;
use App\Services\Academico\MateriaService;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

/** P1 Gestión de Materias: acceso, inventario del wireframe, filtro y acciones. */
class GestionMateriasTest extends TestCase
{
    use ConRolesYPermisos;

    private User $coordinador;

    protected function setUp(): void
    {
        parent::setUp();
        $this->coordinador = $this->crearUsuario(RolSistema::Coordinador);
    }

    private function filas(array $params = []): array
    {
        return $this->actingAs($this->coordinador)
            ->postJson(route('panel.materias.datatable'), ['draw' => 1, 'start' => 0, 'length' => 50, ...$params])
            ->assertOk()->json('data');
    }

    public function test_coordinador_ve_p1(): void
    {
        $this->get(route('panel.materias.index'))->assertRedirect(route('login'));
        $this->actingAs($this->crearUsuario(RolSistema::Preceptor))->get(route('panel.materias.index'))->assertForbidden();
        $this->actingAs($this->coordinador)->get(route('panel.materias.index'))->assertOk();
    }

    public function test_p1_respeta_el_wireframe(): void
    {
        $html = $this->actingAs($this->coordinador)->get(route('panel.materias.index'))
            ->assertSeeInOrder(['GESTIÓN DE MATERIAS', 'Filtrar', 'Buscar por nombre..', '+ Registrar Materia'])
            ->assertSeeInOrder(['NOMBRE DE LA MATERIA', 'ESTADO', 'ACCIONES'])
            ->getContent();

        $this->assertStringContainsString('data-dom="lrtip"', $html, '"Mostrar 10 registros" sí; "Buscar:" de DataTables no.');
        $this->assertStringNotContainsString('&quot;orderable&quot;:true', $html);
        $this->assertStringNotContainsString('data-abrir', $html, 'Sin aviso en la sesión no se abre el modal.');
    }

    public function test_lista_materias_activas_e_inactivas_con_las_dos_acciones(): void
    {
        $matematica = Materia::factory()->create(['nombre_materia' => 'Matemática']);
        Materia::factory()->inactiva()->create(['nombre_materia' => 'Oratoria']);

        $filas = collect($this->filas())->keyBy('nombre_materia');

        $this->assertSame(['Matemática', 'Oratoria'], $filas->keys()->all());
        $this->assertStringContainsString('Activo', $filas['Matemática']['estado']);
        $this->assertStringContainsString('Inactivo', $filas['Oratoria']['estado']);
        $this->assertStringContainsString(route('panel.materias.edit', $matematica), $filas['Matemática']['acciones']);
        $this->assertSame(1, substr_count($filas['Matemática']['acciones'], 'las-btn-deshabilitado'), '🗑 deshabilitado.');
    }

    /** @return array<string, array{string, list<string>}> */
    public static function busquedas(): array
    {
        return [
            'palabras' => ['algoritmos datos', ['Algoritmos y Estructuras de Datos']],
            'porcentaje literal' => ['%', []],
            'guion bajo literal' => ['_', []],
        ];
    }

    #[DataProvider('busquedas')]
    public function test_filtra_por_nombre(string $texto, array $esperadas): void
    {
        Materia::factory()->create(['nombre_materia' => 'Algoritmos y Estructuras de Datos']);
        Materia::factory()->create(['nombre_materia' => 'Matemática']);

        $this->assertSame($esperadas, array_column($this->filas(['buscar' => $texto]), 'nombre_materia'));
    }

    public function test_lista_sin_n_mas_1(): void
    {
        Materia::factory()->count(5)->create();

        DB::enableQueryLog();
        $this->assertCount(5, $this->filas());
        $consultas = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThan(12, $consultas, 'El listado no debe agregar consultas por cada materia.');
    }

    public function test_modal_de_vinculacion_por_sesion(): void
    {
        $this->actingAs($this->coordinador)->withSession([MateriaController::AVISO => MateriaService::MENSAJE_MATERIA_VINCULADA])
            ->get(route('panel.materias.index'))
            ->assertSee('Materia vinculada a un Plan de Estudio')
            ->assertSee(MateriaService::MENSAJE_MATERIA_VINCULADA)
            ->assertSee('data-abrir', false);
    }
}
