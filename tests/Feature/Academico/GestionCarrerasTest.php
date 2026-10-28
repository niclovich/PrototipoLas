<?php

namespace Tests\Feature\Academico;

use App\Enums\EstadoCarrera;
use App\Enums\RolSistema;
use App\Models\Carrera;
use App\Models\Modalidad;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

/** P1 Gestión de Carreras: acceso, inventario del wireframe, filtro y acciones. */
class GestionCarrerasTest extends TestCase
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
            ->postJson(route('panel.carreras.datatable'), ['draw' => 1, 'start' => 0, 'length' => 50, ...$params])
            ->assertOk()->json('data');
    }

    public function test_coordinador_ve_p1(): void
    {
        $this->get(route('panel.carreras.index'))->assertRedirect(route('login'));
        $this->actingAs($this->crearUsuario(RolSistema::Preceptor))->get(route('panel.carreras.index'))->assertForbidden();
        $this->actingAs($this->coordinador)->get(route('panel.carreras.index'))->assertOk();
    }

    public function test_p1_respeta_el_wireframe(): void
    {
        $html = $this->actingAs($this->coordinador)->get(route('panel.carreras.index'))
            ->assertSeeInOrder(['GESTIÓN DE CARRERAS', 'Filtrar', 'Buscar..', 'Registrar Carrera'])
            ->assertSeeInOrder(['CARRERA', 'MODALIDAD', 'DURACIÓN', 'RESOLUCIÓN', 'ESTADO', 'ACCIÓN'])
            ->getContent();

        $this->assertStringNotContainsString('&quot;orderable&quot;:true', $html, 'Sin orden por columna.');
        $this->assertStringContainsString('data-dom="rtip"', $html);
    }

    public function test_lista_todas_las_carreras_con_sus_datos_y_las_tres_acciones(): void
    {
        $presencial = Modalidad::factory()->create(['modalidad' => 'Presencial']);
        $carrera = Carrera::factory()->pendiente()->create([
            'nombre_carrera' => 'Tecnicatura en Programación', 'resolucion_carrera' => '5678/2021', 'modalidad_id' => $presencial->id,
        ]);
        Carrera::factory()->inactiva()->create();

        $filas = collect($this->filas())->keyBy('id');
        $fila = $filas[$carrera->id];

        $this->assertCount(2, $filas);
        $this->assertSame('Presencial', $fila['modalidad']);
        $this->assertSame('Resolución N° 5678/2021', $fila['resolucion']);
        $this->assertStringContainsString('Pendiente', $fila['estado']);
        $this->assertSame(3, substr_count($fila['acciones'], '<i class="fas'), '👁 ✎ 🗑 siempre presentes.');
        $this->assertSame(2, substr_count($fila['acciones'], 'las-btn-deshabilitado'), '👁 y 🗑 deshabilitados.');
        $this->assertStringContainsString(route('panel.carreras.edit', $carrera), $fila['acciones']);
    }

    public function test_sin_permiso_de_editar_no_ve_el_lapiz(): void
    {
        $carrera = Carrera::factory()->create();
        $lector = $this->crearUsuario();
        $lector->givePermissionTo('listar_carrera');

        $acciones = $this->actingAs($lector)->postJson(route('panel.carreras.datatable'), ['draw' => 1, 'start' => 0, 'length' => 10])
            ->json('data.0.acciones');

        $this->assertStringNotContainsString(route('panel.carreras.edit', $carrera), $acciones);
    }

    /** @return array<string, array{string, list<string>}> */
    public static function busquedas(): array
    {
        return [
            'nombre por palabras' => ['analisis sistemas', ['Licenciatura en Analisis de Sistemas']],
            'resolución' => ['1234/2020', ['Licenciatura en Analisis de Sistemas']],
            'resolución como se muestra' => ['Resolución N° 1234/2020', ['Licenciatura en Analisis de Sistemas']],
            'modalidad' => ['distancia', ['Tecnicatura en Programacion']],
            'estado' => ['pendiente', ['Tecnicatura en Programacion']],
            'comodín escapado' => ['%', []],
        ];
    }

    #[DataProvider('busquedas')]
    public function test_filtra_por_nombre_resolucion_modalidad_y_estado(string $texto, array $esperadas): void
    {
        Carrera::factory()->create([
            'nombre_carrera' => 'Licenciatura en Analisis de Sistemas', 'resolucion_carrera' => '1234/2020', 'estado_carrera' => EstadoCarrera::Activo,
            'modalidad_id' => Modalidad::factory()->create(['modalidad' => 'Presencial'])->id,
        ]);
        Carrera::factory()->pendiente()->create([
            'nombre_carrera' => 'Tecnicatura en Programacion', 'resolucion_carrera' => '5678/2021',
            'modalidad_id' => Modalidad::factory()->create(['modalidad' => 'A distancia'])->id,
        ]);

        $this->assertSame($esperadas, array_column($this->filas(['buscar' => $texto]), 'nombre_carrera'));
    }

    public function test_sin_n_mas_1(): void
    {
        Carrera::factory()->count(5)->create();

        DB::enableQueryLog();
        $this->assertCount(5, $this->filas());
        $consultas = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThan(12, $consultas);
    }
}
