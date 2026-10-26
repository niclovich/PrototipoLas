<?php

namespace Database\Factories;

use App\Enums\EstadoGeneral;
use App\Models\Comision;
use App\Models\DetallePlanEstudio;
use App\Models\DetComision;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DetComision>
 */
class DetComisionFactory extends Factory
{
    protected $model = DetComision::class;

    public function definition(): array
    {
        return [
            'comision_id' => Comision::factory(),
            'detalle_plan_estudio_id' => DetallePlanEstudio::factory(),
            'estado_det_comision' => EstadoGeneral::Activo,
        ];
    }
}
