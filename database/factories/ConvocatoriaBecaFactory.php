<?php

namespace Database\Factories;

use App\Enums\EstadoGeneral;
use App\Models\ConvocatoriaBeca;
use App\Models\TipoBeca;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ConvocatoriaBeca>
 */
class ConvocatoriaBecaFactory extends Factory
{
    protected $model = ConvocatoriaBeca::class;

    public function definition(): array
    {
        return [
            'tipo_beca_id' => TipoBeca::factory(),
            'anio_lectivo' => (int) now()->format('Y'),
            'fecha_desde' => now()->startOfYear(),
            'fecha_hasta' => now()->endOfYear(),
            'cantidad_disponible' => 10,
            'estado_convocatoria' => EstadoGeneral::Activo,
        ];
    }
}
