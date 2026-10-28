<?php

namespace Tests\Feature\Modelos;

use App\Models\Cuota;
use App\Models\Mes;
use Database\Seeders\CatalogosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** CuotaFactory reutiliza los Meses precargados (id = orden de Mes::VALORES), con o sin seeder. */
class CuotaFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_sin_seeder_crea_los_meses_con_id_igual_al_orden(): void
    {
        Cuota::factory()->count(5)->create();

        $this->assertSame(array_keys(Mes::VALORES), Mes::orderBy('id')->pluck('descripcion')->all());
        $this->assertSame(range(1, count(Mes::VALORES)), Mes::orderBy('id')->pluck('id')->all());
    }

    public function test_con_seeder_no_duplica_meses(): void
    {
        $this->seed(CatalogosSeeder::class);

        Cuota::factory()->count(15)->create();

        $this->assertSame(count(Mes::VALORES), Mes::count());
        $this->assertSame(15, Cuota::count());
    }
}
