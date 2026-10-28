<?php

namespace Tests\Feature\Personal;

use App\Enums\EstadoCuenta;
use App\Enums\RolSistema;
use App\Models\Motivo;
use App\Models\User;
use App\Services\Personal\PersonalService;
use Database\Seeders\CatalogosSeeder;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

/** CU 27 Registrar Baja de Personal. */
class RegistrarBajaPersonalTest extends TestCase
{
    use ConRolesYPermisos;

    private User $director;

    private User $docente;

    private Motivo $renuncia;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CatalogosSeeder::class);
        $this->director = User::factory()->personal(RolSistema::DirectorGeneral)->create();
        $this->docente = User::factory()->personal(RolSistema::Docente)->create(['email' => 'docente@las.test']);
        $this->renuncia = Motivo::deAccion(Motivo::ACCION_BAJA_PERSONAL)->where('descripcion', 'Renuncia')->sole();
    }

    private function baja(array $datos, ?User $usuario = null)
    {
        $usuario ??= $this->docente;

        return $this->actingAs($this->director)->from(route('panel.personal.index'))
            ->post(route('panel.personal.baja', $usuario), ['usuario_id' => $usuario->id, ...$datos]);
    }

    public function test_registra_baja(): void
    {
        DB::table('sessions')->insert(['id' => 'abierta', 'user_id' => $this->docente->id, 'payload' => '', 'last_activity' => time()]);

        $this->baja(['motivo_id' => $this->renuncia->id, 'observacion_novedad' => 'Presentó la renuncia.'])
            ->assertRedirect(route('panel.personal.confirmacion'))
            ->assertSessionHas('confirmacion', PersonalService::CONFIRMACION_BAJA);

        $this->assertSame(EstadoCuenta::Inactivo, $this->docente->fresh()->estado_cuenta);
        $this->assertDatabaseMissing('sessions', ['user_id' => $this->docente->id]);
        $this->assertDatabaseHas('novedades', [
            'user_id' => $this->docente->id,
            'motivo_id' => $this->renuncia->id,
            'alumno_id' => null,
            'fecha_novedad' => today()->toDateString(),
            'observacion_novedad' => 'Presentó la renuncia.',
        ]);
        $this->assertSame('Renuncia', $this->docente->fresh()->load('ultimaBaja.motivo')->ultimaBaja->motivo->descripcion);
    }

    public function test_observacion_opcional(): void
    {
        $this->baja(['motivo_id' => $this->renuncia->id])->assertSessionHasNoErrors();
        $this->assertSame(EstadoCuenta::Inactivo, $this->docente->fresh()->estado_cuenta);
    }

    public function test_dado_de_baja_no_puede_ingresar(): void
    {
        $this->baja(['motivo_id' => $this->renuncia->id]);
        auth()->logout();

        $this->post(route('login'), ['email' => 'docente@las.test', 'password' => 'Clave1234'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_motivo_obligatorio_y_de_baja_de_personal(): void
    {
        $otro = Motivo::factory()->create(['accion' => 'Otra acción']);

        $this->baja([])->assertSessionHasErrors(['motivo_id' => 'Seleccione un motivo de baja.']);
        $this->baja(['motivo_id' => $otro->id])->assertSessionHasErrors('motivo_id');
        $this->assertSame(EstadoCuenta::Activo, $this->docente->fresh()->estado_cuenta);

        // Al volver con errores, P1 reabre el modal con el mismo usuario.
        $this->followRedirects($this->baja([]))->assertSee('value="'.$this->docente->dni_usuario.'"', false)->assertSee('data-abrir', false);
    }

    public function test_no_puede_darse_de_baja_a_si_mismo(): void
    {
        $this->baja(['motivo_id' => $this->renuncia->id], $this->director)
            ->assertSessionHasErrors(['accion' => 'No puede dar de baja su propio usuario.']);
        $this->assertTrue($this->director->fresh()->estaActivo());
    }

    public function test_no_da_de_baja_inactivo(): void
    {
        $inactivo = User::factory()->personal()->inactivo()->create();

        $this->actingAs($this->director)->post(route('panel.personal.baja', $inactivo->id), ['motivo_id' => $this->renuncia->id])->assertNotFound();
    }
}
