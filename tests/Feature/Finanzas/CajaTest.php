<?php

namespace Tests\Feature\Finanzas;

use App\Enums\EstadoCaja;
use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Http\Controllers\Panel\Finanzas\CajaController;
use App\Models\Caja;
use App\Models\MedioPago;
use App\Models\Pago;
use App\Models\User;
use App\Services\Finanzas\CajaService;
use Database\Seeders\CatalogosSeeder;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

/** CU 35 Apertura de Caja y CU 36 Cierre de Caja. */
class CajaTest extends TestCase
{
    use ConRolesYPermisos;

    private User $cajero;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CatalogosSeeder::class);
        $this->cajero = $this->crearUsuario(RolSistema::Cajero);
        $this->actingAs($this->cajero);
    }

    /** @return array<int, string> contados en 0 para todos los medios, salvo los indicados */
    private function contados(Caja $caja, array $montos = []): array
    {
        return MedioPago::pluck('id')->mapWithKeys(fn ($id) => [$id => (string) ($montos[$id] ?? 0)])->all();
    }

    public function test_apertura_camino_feliz(): void
    {
        $this->get(route('panel.cajas.index'))->assertOk()->assertSee('Abrir Caja');
        $this->get(route('panel.cajas.create'))->assertOk()->assertSee('Registrar Apertura de Caja');

        $this->post(route('panel.cajas.store'), ['monto_inicial' => '$ 5.000,00'])->assertRedirect(route('panel.cajas.confirmacion'));
        $caja = Caja::sole();
        $this->assertSame(EstadoCaja::Abierta, $caja->estado_caja);
        $this->assertEquals(5000, (float) $caja->monto_inicial);
        $this->get(route('panel.cajas.confirmacion'))->assertSee(CajaService::CONFIRMACION_APERTURA);

        $this->postJson(route('panel.cajas.datatable'), ['draw' => 1, 'start' => 0, 'length' => 10, 'estado' => 'Abierta'])
            ->assertOk()->assertJsonPath('recordsFiltered', 1);
    }

    public function test_apertura_con_caja_abierta(): void
    {
        Caja::factory()->create(['user_id' => $this->cajero->id]);

        $this->get(route('panel.cajas.create'))->assertRedirect(route('panel.cajas.index'))
            ->assertSessionHas(CajaController::AVISO, 'Ya tenés una caja abierta');
        $this->post(route('panel.cajas.store'), ['monto_inicial' => '100'])
            ->assertSessionHas(CajaController::AVISO, 'Ya tenés una caja abierta');
        $this->assertSame(1, Caja::count());
    }

    public function test_apertura_monto_invalido(): void
    {
        $this->post(route('panel.cajas.store'), ['monto_inicial' => '0'])
            ->assertSessionHasErrors(['monto_inicial' => 'El monto inicial debe ser mayor a cero']);
    }

    public function test_cierre_camino_feliz(): void
    {
        $caja = Caja::factory()->create(['user_id' => $this->cajero->id, 'monto_inicial' => 5000]);
        $efectivo = MedioPago::where('medio_pago', MedioPago::EFECTIVO)->value('id');
        Pago::factory()->create(['caja_id' => $caja->id, 'medio_pago_id' => $efectivo, 'monto_total' => 120000, 'estado_pago' => EstadoGeneral::Activo]);

        $this->get(route('panel.cajas.show', $caja))->assertOk()->assertSee('$125.000')->assertSee('Confirmar Cierre');

        $this->post(route('panel.cajas.cerrar', $caja), [
            'contados' => $this->contados($caja, [$efectivo => '124500']),
            'observacion_cierre' => 'Faltante de $500 por error en el vuelto.',
        ])->assertRedirect(route('panel.cajas.confirmacion'));

        $caja->refresh();
        $this->assertSame(EstadoCaja::Cerrada, $caja->estado_caja);
        $this->assertEquals(-500, (float) $caja->diferencia_total);
        $this->assertEquals(124500, (float) $caja->monto_final);
        $this->assertSame(MedioPago::count(), $caja->detallesCierre()->count());
        $this->assertEquals(125000, (float) $caja->detallesCierre()->where('medio_pago_id', $efectivo)->value('monto_esperado'));
    }

    public function test_cierre_sin_caja_abierta(): void
    {
        $cerrada = Caja::factory()->create(['user_id' => $this->cajero->id, 'estado_caja' => EstadoCaja::Cerrada, 'fecha_cierre' => now()]);

        $this->post(route('panel.cajas.cerrar', $cerrada), ['contados' => $this->contados($cerrada)])
            ->assertRedirect(route('panel.cajas.index'))
            ->assertSessionHas(CajaController::AVISO, 'No tenés ninguna caja abierta para cerrar');
    }

    public function test_cierre_monto_invalido(): void
    {
        $caja = Caja::factory()->create(['user_id' => $this->cajero->id]);
        $contados = $this->contados($caja);
        $contados[array_key_first($contados)] = '-1';

        $this->post(route('panel.cajas.cerrar', $caja), ['contados' => $contados])
            ->assertSessionHasErrors(['contados.'.array_key_first($contados) => 'El monto contado debe ser un valor mayor o igual a cero']);
    }

    public function test_cierre_sin_observacion(): void
    {
        $caja = Caja::factory()->create(['user_id' => $this->cajero->id, 'monto_inicial' => 5000]);

        // Efectivo esperado 5000 y contado 0: diferencia sin observación.
        $this->post(route('panel.cajas.cerrar', $caja), ['contados' => $this->contados($caja)])
            ->assertSessionHasErrors(['observacion_cierre' => 'Debe ingresar una observación que justifique la diferencia']);
        $this->assertSame(EstadoCaja::Abierta, $caja->refresh()->estado_caja);
    }
}
