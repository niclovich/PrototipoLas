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
        $this->assertFalse($this->servicio->estaVencida($this->cuota(Mes::INSCRIPCION), $this->alumno, CarbonImmutable::create(self::ANIO + 1, 1, 1)));
        $this->assertTrue($this->servicio->cuotasVencidas($this->alumno, CarbonImmutable::create(self::ANIO + 1, 1, 1))->isEmpty());
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
