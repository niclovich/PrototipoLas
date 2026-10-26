<?php

namespace Database\Factories;

use App\Models\TipoActa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TipoActa>
 */
class TipoActaFactory extends Factory
{
    protected $model = TipoActa::class;

    public function definition(): array
    {
        return [
            'descripcion' => 'Tipo de acta '.fake()->unique()->bothify('??-###'),
        ];
    }
}
