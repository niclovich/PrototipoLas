<?php

namespace Tests\Feature\Finanzas;

use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Http\Controllers\Panel\Alumnos\AlumnoController;
use App\Models\Alumno;
use App\Models\Beca;
use App\Models\Caja;
use App\Models\Cuota;
use App\Models\FacturaComprobante;
use App\Models\MedioPago;
use App\Models\Mes;
use App\Models\Pago;
use App\Models\User;
use App\Services\Finanzas\PagoCuotasService;
use Database\Seeders\CatalogosSeeder;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

/** CU7 Registrar Pago de Cuotas (P1 Gestión de Alumnos → P2 → P3 y comprobante). */
class PagoCuotasTest extends TestCase
{
    use ConRolesYPermisos;

    private User $cajero;

    private Alumno $alumno;

    private Cuota $inscripcion;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CatalogosSeeder::class);
        $this->cajero = $this->crearUsuario(RolSistema::Cajero);
        $this->alumno = Alumno::factory()->create();
        $this->inscripcion = Cuota::factory()->create([
            'carrera_id' => $this->alumno->carrera_id, 'mes_id' => Mes::inscripcion()->id, 'anio' => now()->year, 'monto' => 67000,
        ]);
        Cuota::factory()->create(['carrera_id' => $this->alumno->carrera_id, 'mes_id' => Mes::where('descripcion', 'Marzo')->value('id'), 'anio' => now()->year, 'monto' => 67000]);
        $this->actingAs($this->cajero);
    }

    private function abrirCaja(): Caja
    {
        return Caja::factory()->create(['user_id' => $this->cajero->id]);
    }

    private function efectivo(): int
    {
        return MedioPago::where('medio_pago', MedioPago::EFECTIVO)->value('id');
    }

    public function test_camino_feliz_con_beca(): void
    {
        $caja = $this->abrirCaja();
        Beca::factory()->create(['alumno_id' => $this->alumno->id, 'valor_beca' => 10]);

        // Preinscripto: solo la Inscripción está pendiente (§7-7).
        $this->get(route('panel.pagos.create', $this->alumno))->assertOk()
            ->assertSee('Beca Activa')->assertSee('INSCRIPCI')->assertDontSee('MARZO');

        $this->post(route('panel.pagos.store', $this->alumno), [
            'cuotas' => [$this->inscripcion->id], 'medio_pago_id' => $this->efectivo(), 'importe' => '60300',
        ])->assertRedirect(route('panel.pagos.confirmacion'));

        $pago = Pago::with(['detalles', 'factura'])->sole();
        $this->assertSame($caja->id, $pago->caja_id);
        $this->assertSame(EstadoGeneral::Activo, $pago->estado_pago);
        $this->assertEquals(60300, (float) $pago->monto_total);
        $this->assertEquals(6700, (float) $pago->detalles->first()->monto_bonificado);
        $this->assertNotNull($pago->detalles->first()->beca_id);
        $this->assertSame(1, $pago->factura->numero_comprobante);
        $this->assertSame('0001-00000001', $pago->factura->numeroFormateado());

        $this->get(route('panel.pagos.confirmacion'))->assertOk()
            ->assertSee(PagoCuotasService::CONFIRMACION)->assertSee('Descargar Comprobante');

        // Ya pagó la Inscripción: ahora debe las mensuales.
        $this->get(route('panel.pagos.create', $this->alumno))->assertOk()->assertSee('MARZO')->assertSee('Fac. 0001-00000001');
    }

    public function test_comprobante_pdf_y_validacion_publica(): void
    {
        $this->abrirCaja();
        $this->post(route('panel.pagos.store', $this->alumno), [
            'cuotas' => [$this->inscripcion->id], 'medio_pago_id' => $this->efectivo(), 'importe' => '67000',
        ]);
        $factura = FacturaComprobante::sole();

        $this->get(route('panel.pagos.comprobante', $factura))->assertOk()->assertHeader('content-type', 'application/pdf');

        auth()->logout();
        $this->get(route('validar.comprobante', $factura->uuid))->assertOk()->assertSee('0001-00000001');
    }

    public function test_sin_caja_abierta(): void
    {
        $this->get(route('panel.pagos.create', $this->alumno))
            ->assertRedirect(route('panel.alumnos.index'))
            ->assertSessionHas(AlumnoController::AVISO, 'No se puede registrar el pago porque no existe una caja abierta');
    }

    public function test_sin_cuotas_pendientes(): void
    {
        $this->abrirCaja();
        $this->inscripcion->update(['estado_cuota' => EstadoGeneral::Inactivo]);

        $this->get(route('panel.pagos.create', $this->alumno))
            ->assertRedirect(route('panel.alumnos.index'))
            ->assertSessionHas(AlumnoController::AVISO, 'El Alumno no posee cuotas pendientes de pago');
    }

    public function test_importe_distinto_del_total(): void
    {
        $this->abrirCaja();

        $this->post(route('panel.pagos.store', $this->alumno), [
            'cuotas' => [$this->inscripcion->id], 'medio_pago_id' => $this->efectivo(), 'importe' => '1000',
        ])->assertSessionHasErrors(['importe' => PagoCuotasService::MENSAJE_IMPORTE]);

        $this->assertSame(0, Pago::count());
    }
}
