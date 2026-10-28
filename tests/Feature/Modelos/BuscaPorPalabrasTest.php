<?php

namespace Tests\Feature\Modelos;

use App\Models\Carrera;
use App\Models\Modalidad;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Trait BuscaPorPalabras (filtros "Buscar.." de las pantallas). */
class BuscaPorPalabrasTest extends TestCase
{
    use RefreshDatabase;

    private function nombres(string $texto, array $columnas, array $relaciones = [], array $ignorar = []): array
    {
        return Carrera::query()->buscarPalabras($texto, $columnas, $relaciones, $ignorar)->orderBy('nombre_carrera')->pluck('nombre_carrera')->all();
    }

    public function test_and_entre_palabras_or_entre_columnas_relacion_ignorar_y_escape(): void
    {
        Carrera::factory()->create(['nombre_carrera' => 'Tecnicatura en Redes', 'resolucion_carrera' => '1/2020',
            'modalidad_id' => Modalidad::factory()->create(['modalidad' => 'Presencial'])->id]);
        Carrera::factory()->create(['nombre_carrera' => 'Tecnicatura en Software_100%', 'resolucion_carrera' => '2/2021',
            'modalidad_id' => Modalidad::factory()->create(['modalidad' => 'A distancia'])->id]);

        $this->assertSame(['Tecnicatura en Redes', 'Tecnicatura en Software_100%'], $this->nombres('', ['nombre_carrera']));
        $this->assertSame(['Tecnicatura en Redes'], $this->nombres('tecnicatura, redes', ['nombre_carrera']));
        $this->assertSame([], $this->nombres('redes 2021', ['nombre_carrera', 'resolucion_carrera']));
        $this->assertSame(['Tecnicatura en Software_100%'], $this->nombres('software 2021', ['nombre_carrera', 'resolucion_carrera']));
        $this->assertSame(['Tecnicatura en Software_100%'], $this->nombres('distancia', ['nombre_carrera'], ['modalidad' => ['modalidad']]));
        $this->assertSame(['Tecnicatura en Redes'], $this->nombres('Resolución N° 1/2020', ['resolucion_carrera'], [], ['resolución', 'n°']));
        $this->assertSame(['Tecnicatura en Software_100%'], $this->nombres('_100%', ['nombre_carrera']));
        $this->assertSame([], $this->nombres('%', ['resolucion_carrera']));
    }
}
