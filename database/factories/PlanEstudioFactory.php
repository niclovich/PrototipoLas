<?php

namespace Database\Factories;

use App\Enums\EstadoGeneral;
use App\Models\Carrera;
use App\Models\PlanEstudio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlanEstudio>
 */
class PlanEstudioFactory extends Factory
{
    protected $model = PlanEstudio::class;

    public function definition(): array
    {
        return [
            'carrera_id' => Carrera::factory(),
            'anio_plan_estudio' => fake()->unique()->numberBetween(1990, 2099),
            'estado_plan_estudio' => EstadoGeneral::Activo,
        ];
    }

    public function activo(): static
    {
        return $this->state(fn () => ['estado_plan_estudio' => EstadoGeneral::Activo]);
    }

    public function inactivo(): static
    {
        return $this->state(fn () => ['estado_plan_estudio' => EstadoGeneral::Inactivo]);
    }
}
