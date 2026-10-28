<?php

namespace Database\Factories;

use App\Enums\EstadoGeneral;
use App\Models\Cuatrimestre;
use App\Models\DetallePlanEstudio;
use App\Models\Materia;
use App\Models\PlanEstudio;
use App\Models\Regimen;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DetallePlanEstudio>
 */
class DetallePlanEstudioFactory extends Factory
{
    protected $model = DetallePlanEstudio::class;

    public function definition(): array
    {
        return [
            'plan_estudio_id' => PlanEstudio::factory(),
            'materia_id' => Materia::factory(),
            'regimen_id' => Regimen::factory(),
            'cuatrimestre_id' => Cuatrimestre::factory(),
            'codigo_det_plan_estudio' => fake()->unique()->bothify('MAT-####'),
            'carga_horaria_det_plan_estudio' => fake()->numberBetween(32, 160),
            'anio_curso_det_plan_estudio' => fake()->numberBetween(1, 3),
            'estado_det_plan_estudio' => EstadoGeneral::Activo,
        ];
    }

    public function inactivo(): static
    {
        return $this->state(fn () => ['estado_det_plan_estudio' => EstadoGeneral::Inactivo]);
    }
}
