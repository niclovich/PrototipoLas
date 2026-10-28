<?php

namespace Tests\Feature\Finanzas;

use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Models\Carrera;
use App\Models\Cuota;
use App\Models\DetallePagoCuota;
use App\Models\Mes;
use App\Models\User;
use App\Services\Finanzas\CuotaService;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

/** CU 33 Registrar y CU 34 Modificar datos de Cuotas por Carrera. */
class CuotasPorCarreraTest extends TestCase
{
    use ConRolesYPermisos;

    private User $director;

    private Carrera $carrera;

    private int $anio;

    protected function setUp(): void
    {
        parent::setUp();
        Mes::precargados();
        $this->director = $this->crearUsuario(RolSistema::DirectorGeneral);
        $this->carrera = Carrera::factory()->create(['nombre_carrera' => 'Licenciatura en Análisis de Sistemas']);
        $this->anio = (int) now()->format('Y');
    }

    private function marzo(): int
    {
        return Mes::where('descripcion', 'Marzo')->value('id');
    }

    private function registrar(array $cambios = [])
    {
        return $this->actingAs($this->director)->from(route('panel.cuotas.create', $this->carrera))
            ->post(route('panel.cuotas.store', $this->carrera), ['mes_id' => $this->marzo(), 'anio' => $this->anio, 'monto' => '45000', ...$cambios]);
    }

    private function cuota(array $atributos = []): Cuota
    {
        return Cuota::factory()->create(['carrera_id' => $this->carrera->id, 'mes_id' => $this->marzo(), 'monto' => 45000, 'anio' => $this->anio, ...$atributos]);
    }

    public function test_p1_lista_las_cuotas_de_la_carrera_y_anio(): void
    {
        $this->cuota();
        $this->cuota(['anio' => $this->anio + 1]);

        $this->actingAs($this->director)->get(route('panel.cuotas.index', ['carrera' => $this->carrera->id]))->assertOk()
            ->assertSeeInOrder(['GESTIÓN DE CUOTAS', 'Seleccionar Carrera', 'Año Lectivo', 'Registrar Cuota', 'MES', 'AÑO', 'MONTO', 'ESTADO', 'ACCIÓN']);

        $filas = $this->actingAs($this->director)->post(route('panel.cuotas.datatable'), ['carrera' => $this->carrera->id, 'anio' => $this->anio, 'draw' => 1])->json('data');
        $this->assertCount(1, $filas);
        $this->assertSame(['Marzo', '$45.000'], [$filas[0]['mes'], $filas[0]['monto_texto']]);
    }

    public function test_registra_la_cuota_activa(): void
    {
        $this->actingAs($this->director)->get(route('panel.cuotas.create', $this->carrera))->assertOk()
            ->assertSeeInOrder(['Registrar Cuota', 'Datos de la Cuota', 'Carrera', 'Mes', 'Seleccione un mes', 'Año', 'Monto', 'Cancelar', 'Registrar Cuota']);

        $this->followRedirects($this->registrar(['monto' => '45.000,50']))
            ->assertSeeInOrder([CuotaService::CONFIRMACION_REGISTRO, 'Mes: Marzo', 'Monto: $45.000,50', 'Aceptar']);

        $cuota = Cuota::sole();
        $this->assertSame(EstadoGeneral::Activo, $cuota->estado_cuota);
        $this->assertSame('45000.50', $cuota->monto);
        $this->assertSame([$this->carrera->id, $this->marzo(), $this->anio], [$cuota->carrera_id, $cuota->mes_id, $cuota->anio]);
    }

    public function test_cu33_alt_7_anio_menor_al_actual(): void
    {
        $this->registrar(['anio' => $this->anio - 1])->assertSessionHasErrors(['anio' => 'El año lectivo no puede ser menor al año actual']);
    }

    public function test_cu33_alt_10_cuota_activa_existente(): void
    {
        $this->cuota();
        $this->registrar()->assertSessionHasErrors(['mes_id' => 'Ya existe una cuota activa para esta combinación de carrera, año lectivo y mes']);
        $this->followRedirects($this->registrar())->assertSee('Cuota existente');

        Cuota::query()->update(['estado_cuota' => EstadoGeneral::Inactivo->value]);
        $this->registrar()->assertSessionHasNoErrors();
    }

    public function test_cu33_alt_13_monto_invalido(): void
    {
        foreach (['0', 'abc', '10,555'] as $monto) {
            $this->registrar(['monto' => $monto])->assertSessionHasErrors(['monto' => 'El monto ingresado no es válido']);
        }
    }

    public function test_modifica_solo_el_monto(): void
    {
        $cuota = $this->cuota();

        $this->actingAs($this->director)->get(route('panel.cuotas.edit', $cuota))->assertOk()
            ->assertSeeInOrder(['Modificar Cuota', 'Datos de la Cuota', 'Carrera', 'Año Lectivo', 'Mes', 'Monto Actual', '$45.000', 'Nuevo Monto', 'Cancelar', 'Guardar Cambios']);

        $this->followRedirects($this->actingAs($this->director)->put(route('panel.cuotas.update', $cuota), ['monto' => '48000', 'anio' => 2000, 'mes_id' => 5]))
            ->assertSeeInOrder([CuotaService::CONFIRMACION_MODIFICACION, 'Monto anterior: $45.000 · Monto nuevo: $48.000']);

        $cuota->refresh();
        $this->assertSame(['48000.00', $this->anio, $this->marzo(), EstadoGeneral::Activo], [$cuota->monto, $cuota->anio, $cuota->mes_id, $cuota->estado_cuota]);
    }

    public function test_cu34_alt_7_anio_anterior(): void
    {
        $cuota = $this->cuota(['anio' => $this->anio - 1]);

        $this->actingAs($this->director)->get(route('panel.cuotas.edit', $cuota))
            ->assertRedirect(route('panel.cuotas.index', ['carrera' => $this->carrera->id, 'anio' => $this->anio - 1]))
            ->assertSessionHas('aviso_cuota', 'No se pueden modificar cuotas de años lectivos anteriores');
        $this->actingAs($this->director)->put(route('panel.cuotas.update', $cuota), ['monto' => '1'])
            ->assertSessionHas('aviso_cuota', 'No se pueden modificar cuotas de años lectivos anteriores');
    }

    public function test_cu34_alt_11_cuota_con_pagos(): void
    {
        $cuota = $this->cuota();
        DetallePagoCuota::factory()->create(['cuota_id' => $cuota->id]);

        $this->followRedirects($this->actingAs($this->director)->get(route('panel.cuotas.edit', $cuota)))
            ->assertSee('La cuota no puede modificarse porque posee pagos registrados');
        $this->actingAs($this->director)->put(route('panel.cuotas.update', $cuota), ['monto' => '1'])
            ->assertSessionHas('aviso_cuota', 'La cuota no puede modificarse porque posee pagos registrados');
        $this->assertSame('45000.00', $cuota->fresh()->monto);
    }

    public function test_cu34_alt_15_monto_invalido(): void
    {
        $cuota = $this->cuota();
        $this->actingAs($this->director)->from(route('panel.cuotas.edit', $cuota))->put(route('panel.cuotas.update', $cuota), ['monto' => '-5'])
            ->assertSessionHasErrors(['monto' => 'El monto ingresado no es válido']);
    }
}
