<?php

namespace Tests\Feature\Finanzas;

use App\Enums\EstadoGeneral;
use App\Models\Alumno;
use App\Models\Carrera;
use App\Models\Cuota;
use App\Models\DetallePagoCuota;
use App\Models\Mes;
use App\Models\Pago;
use App\Services\Finanzas\DeudaCuotasService;
use Carbon\CarbonImmutable;
use Database\Seeders\CatalogosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Aclaración "Meses y vencimiento de las Cuotas" (CU 33): vencimiento, Inscripción y deuda. */
class DeudaCuotasServiceTest extends TestCase
{
    use RefreshDatabase;

    private const ANIO = 2026;

    private DeudaCuotasService $servicio;

    private Alumno $alumno;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CatalogosSeeder::class);
        $this->servicio = app(DeudaCuotasService::class);
        $carrera = Carrera::factory()->create();
        $this->alumno = Alumno::factory()->create(['carrera_id' => $carrera->id]);

        foreach (Mes::orderBy('id')->get() as $mes) {
            Cuota::factory()->create(['carrera_id' => $carrera->id, 'mes_id' => $mes->id, 'anio' => self::ANIO]);
        }
    }

    public function test_las_mensuales_vencen_el_dia_10_y_la_inscripcion_no_vence(): void
    {
        $this->assertNull($this->servicio->vencimiento($this->cuota(Mes::INSCRIPCION)));
        $this->assertSame('10/03/2026', $this->servicio->vencimiento($this->cuota('Marzo'))->format('d/m/Y'));
        $this->assertSame('10/12/2026', $this->servicio->vencimiento($this->cuota('Diciembre'))->format('d/m/Y'));
    }

    public function test_inscripcion_pagada_y_preinscripto_solo_debe_la_inscripcion(): void
    {
        $this->travelTo(CarbonImmutable::create(self::ANIO, 2, 1));

        $this->assertFalse($this->servicio->inscripcionPagada($this->alumno, self::ANIO));
        $this->assertSame([Mes::INSCRIPCION], $this->servicio->cuotasPendientes($this->alumno)->map->mes->pluck('descripcion')->all());
        $this->assertFalse($this->servicio->tieneCuotasVencidas($this->alumno));

        $this->pagar($this->cuota(Mes::INSCRIPCION));

        $this->assertTrue($this->servicio->inscripcionPagada($this->alumno, self::ANIO));
        $this->assertSame(self::ANIO, $this->servicio->anioIngreso($this->alumno));
        $this->assertCount(10, $this->servicio->cuotasPendientes($this->alumno), 'Debe todas las mensuales del año.');
    }

    public function test_vencida_si_paso_el_vencimiento_y_no_esta_cancelada_por_un_pago_activo(): void
    {
        $this->pagar($this->cuota(Mes::INSCRIPCION));
        $marzo = $this->cuota('Marzo');

        $this->assertFalse($this->servicio->estaVencida($marzo, $this->alumno, CarbonImmutable::create(self::ANIO, 3, 10, 23)));
        $this->assertTrue($this->servicio->estaVencida($marzo, $this->alumno, CarbonImmutable::create(self::ANIO, 3, 11)));
        $this->assertSame(['Marzo', 'Abril'], $this->servicio->cuotasVencidas($this->alumno, CarbonImmutable::create(self::ANIO, 4, 11))->map->mes->pluck('descripcion')->all());

        $this->pagar($marzo, EstadoGeneral::Inactivo);
        $this->assertTrue($this->servicio->estaVencida($marzo, $this->alumno, CarbonImmutable::create(self::ANIO, 3, 11)), 'Un Pago Inactivo no cancela.');

        $this->pagar($marzo);
        $this->assertFalse($this->servicio->estaVencida($marzo, $this->alumno, CarbonImmutable::create(self::ANIO, 3, 11)));
    }

    public function test_la_inscripcion_nunca_cuenta_como_vencida(): void
    {
        // Alumno con ingreso 2026 (Inscripción pagada), Inscripción 2027 impaga y todas las mensuales vencidas.
        $this->pagar($this->cuota(Mes::INSCRIPCION));
        $inscripcion2027 = Cuota::factory()->create(['carrera_id' => $this->alumno->carrera_id, 'mes_id' => Mes::inscripcion()->id, 'anio' => self::ANIO + 1]);
        $enero2028 = CarbonImmutable::create(self::ANIO + 2, 1, 15);

        $vencidas = $this->servicio->cuotasVencidas($this->alumno, $enero2028);

        $this->assertCount(10, $vencidas, 'Las 10 mensuales de 2026 están vencidas.');
        $this->assertFalse($vencidas->contains(fn (Cuota $c) => $c->mes->esInscripcion()));
        $this->assertNotContains($inscripcion2027->id, $vencidas->pluck('id'));
        $this->assertFalse($this->servicio->estaVencida($inscripcion2027, $this->alumno, $enero2028));
        $this->assertFalse($this->servicio->estaVencida($this->cuota(Mes::INSCRIPCION), $this->alumno, $enero2028));
    }

    public function test_esta_vencida_usa_la_misma_regla_que_cuotas_vencidas(): void
    {
        $abril = CarbonImmutable::create(self::ANIO, 4, 20);
        $marzo = $this->cuota('Marzo');

        // Preinscripto (sin Inscripción pagada): ninguna mensual cuenta como vencida.
        $this->assertFalse($this->servicio->estaVencida($marzo, $this->alumno, $abril));
        $this->assertTrue($this->servicio->cuotasVencidas($this->alumno, $abril)->isEmpty());

        // Mensual de un año anterior al ingreso: no se debe.
        $this->pagar($this->cuota(Mes::INSCRIPCION));
        $marzoAnterior = Cuota::factory()->create(['carrera_id' => $this->alumno->carrera_id, 'mes_id' => $marzo->mes_id, 'anio' => self::ANIO - 1]);
        $this->assertFalse($this->servicio->estaVencida($marzoAnterior, $this->alumno, $abril));

        // Con ingreso: ambas formas dicen lo mismo para cada cuota mensual del año.
        $vencidas = $this->servicio->cuotasVencidas($this->alumno, $abril)->pluck('id')->all();
        foreach (Cuota::mensuales()->where('anio', self::ANIO)->get() as $cuota) {
            $this->assertSame(in_array($cuota->id, $vencidas, true), $this->servicio->estaVencida($cuota, $this->alumno, $abril), $cuota->mes->descripcion);
        }
        $this->assertSame(['Marzo', 'Abril'], Cuota::whereIn('id', $vencidas)->with('mes')->orderBy('mes_id')->get()->pluck('mes.descripcion')->all());
    }

    public function test_filtrar_vencidas_en_listados_sin_n_mas_1(): void
    {
        $this->pagar($this->cuota(Mes::INSCRIPCION));
        $this->pagar($this->cuota('Marzo'));
        $cuotas = Cuota::query()->orderBy('mes_id')->get();

        DB::enableQueryLog();
        $vencidas = $this->servicio->filtrarVencidas($this->alumno, $cuotas, CarbonImmutable::create(self::ANIO, 12, 31));
        $consultas = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertCount(9, $vencidas, 'Abril a Diciembre (Marzo está pagada; la Inscripción no vence).');
        $this->assertLessThanOrEqual(3, $consultas, 'Año de ingreso + meses + cancelaciones, sin importar la cantidad de cuotas.');
    }

    public function test_scopes_de_inscripcion_y_mensuales(): void
    {
        $this->assertSame(1, Cuota::deInscripcion()->count());
        $this->assertSame(10, Cuota::mensuales()->count());
        $this->assertSame(Mes::INSCRIPCION, Mes::inscripcion()->descripcion);
    }

    private function cuota(string $mes): Cuota
    {
        return Cuota::whereHas('mes', fn ($q) => $q->where('descripcion', $mes))->firstOrFail();
    }

    private function pagar(Cuota $cuota, EstadoGeneral $estado = EstadoGeneral::Activo): void
    {
        $pago = Pago::factory()->create(['alumno_id' => $this->alumno->id, 'estado_pago' => $estado]);
        DetallePagoCuota::factory()->create(['pago_id' => $pago->id, 'cuota_id' => $cuota->id]);
    }
}
