<?php

namespace Database\Factories;

use App\Models\Alumno;
use App\Models\ConvocatoriaBeca;
use App\Models\SolicitudBeca;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SolicitudBeca>
 */
class SolicitudBecaFactory extends Factory
{
    protected $model = SolicitudBeca::class;

    public function definition(): array
    {
        return [
            'alumno_id' => Alumno::factory(),
            'convocatoria_beca_id' => ConvocatoriaBeca::factory(),
            'situacion_economica' => fake()->sentence(),
            'motivo_solicitud' => fake()->sentence(),
            'fecha_solicitud' => today(),
            'estado_solicitud' => 'Aprobada',
        ];
    }
}
