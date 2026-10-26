<?php

namespace Database\Factories;

use App\Enums\CondicionCorrelativa;
use App\Models\Correlativa;
use App\Models\DetallePlanEstudio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Correlativa>
 */
class CorrelativaFactory extends Factory
{
    protected $model = Correlativa::class;

    public function definition(): array
    {
        return [
            'detalle_plan_estudio_id' => DetallePlanEstudio::factory(),
            'detalle_plan_correlativa_id' => DetallePlanEstudio::factory(),
            'p_cursar' => CondicionCorrelativa::Regular,
            'p_rendir' => CondicionCorrelativa::Aprobada,
        ];
    }
}
