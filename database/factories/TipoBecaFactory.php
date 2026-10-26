<?php

namespace Database\Factories;

use App\Enums\EstadoGeneral;
use App\Models\TipoBeca;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TipoBeca>
 */
class TipoBecaFactory extends Factory
{
    protected $model = TipoBeca::class;

    public function definition(): array
    {
        return [
            'nombre_beca' => 'Beca '.fake()->word(),
            'tipo_descuento' => 'Porcentaje',
            'valor_descuento' => 10,
            'estado_beca' => EstadoGeneral::Activo,
        ];
    }
}
