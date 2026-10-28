<?php

namespace Tests\Feature\Personal;

use App\Enums\RolSistema;
use App\Models\Novedad;
use App\Models\User;
use Database\Seeders\CatalogosSeeder;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

/** P1 Gestión de Personal: acceso, inventario del wireframe, pestañas y filtros. */
class GestionPersonalTest extends TestCase
{
    use ConRolesYPermisos;

    private User $director;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CatalogosSeeder::class);
        $this->director = User::factory()->personal(RolSistema::DirectorGeneral)->create();
    }

    private function filas(array $params): array
    {
        return $this->actingAs($this->director)
            ->postJson(route('panel.personal.datatable'), ['draw' => 1, 'start' => 0, 'length' => 50, ...$params])
            ->assertOk()->json('data');
    }

    public function test_acceso_a_p1(): void
    {
        $this->get(route('panel.personal.index'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->personal(RolSistema::Cajero)->create())->get(route('panel.personal.index'))->assertForbidden();
        $this->actingAs($this->director)->get(route('panel.personal.index'))->assertOk();
    }

    public function test_p1_respeta_el_wireframe(): void
    {
        $this->actingAs($this->director)->get(route('panel.personal.index'))
            ->assertSeeInOrder(['GESTIÓN DE PERSONAL', 'Activos', 'Eliminados', 'Registrar Personal', 'Filtrar', 'Buscar por DNI o nombre..', 'Rol: Todos'])
            ->assertSeeInOrder(['DNI', 'APELLIDO Y NOMBRE', 'ROL', 'CORREO', 'TELÉFONO', 'ESTADO', 'ACCIONES'])
            ->assertSeeInOrder(['DNI', 'APELLIDO Y NOMBRE', 'ROL', 'FECHA ALTA', 'FECHA BAJA', 'MOTIVO DE BAJA', 'ESTADO', 'ACCIONES'])
            ->assertSeeInOrder(['Registrar Baja de Personal', 'DATOS DEL USUARIO', 'DNI', 'Apellido y Nombre', 'Rol', 'Fecha de Alta', 'Estado Cuenta',
                'DATOS DE LA BAJA', 'Fecha', 'Motivo de Baja', 'Observación', 'Cancelar', 'Confirmar Baja']);
    }

    public function test_el_filtro_de_rol_ofrece_solo_los_roles_del_personal(): void
    {
        $html = $this->actingAs($this->director)->get(route('panel.personal.index'))->getContent();

        foreach (RolSistema::valoresDelPersonal() as $rol) {
            $this->assertStringContainsString('<option value="'.$rol.'">', $html);
        }
        $this->assertStringNotContainsString('<option value="Alumno">', $html);
        $this->assertStringNotContainsString('<option value="SUPERADMIN">', $html);
    }

    public function test_activos_lista_solo_personal_activo(): void
    {
        $docente = User::factory()->personal(RolSistema::Docente)->create();
        User::factory()->personal(RolSistema::Cajero)->inactivo()->create();
        $this->crearUsuario(RolSistema::Alumno);
        $this->superAdmin();

        $ids = array_column($this->filas(['pestania' => 'activos']), 'id');

        $this->assertEqualsCanonicalizing([$this->director->id, $docente->id], $ids);
    }

    public function test_eliminados_muestra_fecha_y_motivo_de_baja_sin_n_mas_1(): void
    {
        foreach (range(1, 3) as $i) {
            $baja = User::factory()->personal(RolSistema::Docente)->inactivo()->create(['fecha_alta' => '2020-03-01']);
            Novedad::factory()->bajaDePersonal()->create(['user_id' => $baja->id, 'fecha_novedad' => '2026-07-15']);
        }

        DB::enableQueryLog();
        $filas = $this->filas(['pestania' => 'eliminados']);
        $consultas = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertCount(3, $filas);
        $this->assertSame('01/03/2020', $filas[0]['fecha_alta_texto']);
        $this->assertSame('15/07/2026', $filas[0]['fecha_baja']);
        $this->assertNotSame('—', $filas[0]['motivo_baja']);
        $this->assertStringContainsString('Inactivo', $filas[0]['estado']);
        $this->assertStringContainsString('Fuera del alcance del prototipo', $filas[0]['acciones']);
        $this->assertLessThan(15, $consultas, 'Las relaciones se precargan (sin N+1).');
    }

    public function test_filtra_por_dni_o_nombre_y_por_rol(): void
    {
        $maria = User::factory()->personal(RolSistema::Docente)->create(['dni_usuario' => '30125478', 'apellido_usuario' => 'Gonzalez', 'nombre_usuario' => 'Maria']);
        $carlos = User::factory()->personal(RolSistema::Cajero)->create(['dni_usuario' => '28456123', 'apellido_usuario' => 'Lopez', 'nombre_usuario' => 'Carlos']);

        $this->assertSame([$maria->id], array_column($this->filas(['pestania' => 'activos', 'buscar' => '30125']), 'id'));
        $this->assertSame([$carlos->id], array_column($this->filas(['pestania' => 'activos', 'buscar' => 'carl']), 'id'));
        $this->assertSame([$carlos->id], array_column($this->filas(['pestania' => 'activos', 'rol' => RolSistema::Cajero->value]), 'id'));
        $this->assertCount(3, $this->filas(['pestania' => 'activos', 'rol' => 'inventado']));

        // Palabra por palabra, como se muestra la columna ("Apellido, Nombre") o al revés.
        $this->assertSame([$maria->id], array_column($this->filas(['pestania' => 'activos', 'buscar' => 'Gonzalez, Maria']), 'id'));
        $this->assertSame([$maria->id], array_column($this->filas(['pestania' => 'activos', 'buscar' => 'maria gonz']), 'id'));
        $this->assertSame([], $this->filas(['pestania' => 'activos', 'buscar' => 'Maria Lopez']));
        // % y _ se buscan literalmente.
        $this->assertSame([], $this->filas(['pestania' => 'activos', 'buscar' => '%']));
        $this->assertSame([], $this->filas(['pestania' => 'activos', 'buscar' => '_']));
    }

    public function test_los_encabezados_no_ordenan(): void
    {
        $html = $this->actingAs($this->director)->get(route('panel.personal.index'))->getContent();

        $this->assertStringNotContainsString('&quot;orderable&quot;:true', $html);
        $this->assertStringContainsString('data-orden="[]"', $html);
    }

    public function test_acciones_de_activos(): void
    {
        $docente = User::factory()->personal(RolSistema::Docente)->create();

        $filas = collect($this->filas(['pestania' => 'activos']))->keyBy('id');

        $this->assertStringContainsString(route('panel.personal.edit', $docente), $filas[$docente->id]['acciones']);
        $this->assertStringContainsString(route('panel.personal.baja', $docente), $filas[$docente->id]['acciones']);
        // El wireframe muestra ✎ y 🗑 en todas las filas: en la propia, el 🗑 está deshabilitado con el motivo (D11).
        $propia = $filas[$this->director->id]['acciones'];
        $this->assertStringContainsString('fa-trash-alt', $propia);
        $this->assertStringContainsString('disabled', $propia);
        $this->assertStringContainsString('No puede dar de baja su propio usuario.', $propia);
        $this->assertStringNotContainsString('data-baja', $propia);
        $this->assertStringContainsString('fa-trash-alt', $filas[$docente->id]['acciones']);
    }
}
