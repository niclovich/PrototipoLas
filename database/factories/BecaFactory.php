<?php

namespace Database\Factories;

use App\Enums\EstadoGeneral;
use App\Models\Alumno;
use App\Models\Beca;
use App\Models\ConvocatoriaBeca;
use App\Models\SolicitudBeca;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Beca>
 */
class BecaFactory extends Factory
{
    protected $model = Beca::class;

    public function definition(): array
    {
        return [
            'alumno_id' => Alumno::factory(),
            'convocatoria_beca_id' => ConvocatoriaBeca::factory(),
            'solicitud_beca_id' => fn (array $a) => SolicitudBeca::factory()->create(['alumno_id' => $a['alumno_id'], 'convocatoria_beca_id' => $a['convocatoria_beca_id']])->id,
            'fecha_inicio' => now()->startOfYear(),
            'fecha_fin' => now()->endOfYear(),
            'valor_beca' => 10,
            'estado_beca' => EstadoGeneral::Activo,
        ];
    }
}
