<?php

namespace Database\Factories;

use App\Enums\CondicionCurricular;
use App\Models\Alumno;
use App\Models\DetallePlanEstudio;
use App\Models\EstadoCurricular;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EstadoCurricular>
 */
class EstadoCurricularFactory extends Factory
{
    protected $model = EstadoCurricular::class;

    public function definition(): array
    {
        return [
            'alumno_id' => Alumno::factory(),
            'detalle_plan_estudio_id' => DetallePlanEstudio::factory(),
            'condicion' => CondicionCurricular::Regular,
            'fecha_condicion' => today(),
        ];
    }
}
