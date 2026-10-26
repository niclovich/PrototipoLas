<?php

namespace Database\Factories;

use App\Enums\EstadoGeneral;
use App\Models\Carrera;
use App\Models\Cuota;
use App\Models\Mes;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cuota>
 */
class CuotaFactory extends Factory
{
    protected $model = Cuota::class;

    public function definition(): array
    {
        return [
            'carrera_id' => Carrera::factory(),
            'monto' => fake()->randomFloat(2, 20000, 150000),
            'mes_id' => Mes::factory(),
            'anio' => (int) now()->format('Y'),
            'estado_cuota' => EstadoGeneral::Activo,
        ];
    }
}
