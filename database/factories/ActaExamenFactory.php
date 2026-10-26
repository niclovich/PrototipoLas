<?php

namespace Database\Factories;

use App\Enums\Asentada;
use App\Enums\EstadoActa;
use App\Models\ActaExamen;
use App\Models\DetallePlanEstudio;
use App\Models\Llamado;
use App\Models\Modalidad;
use App\Models\TipoActa;
use App\Models\Tribunal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActaExamen>
 */
class ActaExamenFactory extends Factory
{
    protected $model = ActaExamen::class;

    public function definition(): array
    {
        return [
            'nro_acta' => fake()->unique()->numberBetween(1, 999999),
            'detalle_plan_estudio_id' => DetallePlanEstudio::factory(),
            'modalidad_id' => Modalidad::factory(),
            'llamado_id' => Llamado::factory(),
            'tipo_acta_id' => TipoActa::factory(),
            'tribunal_id' => Tribunal::factory(),
            'fecha_acta' => today()->addMonth(),
            'estado_acta' => EstadoActa::Pendiente,
            'asentada' => Asentada::No,
        ];
    }
}
