<?php

namespace Database\Factories;

use App\Models\Cuatrimestre;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cuatrimestre>
 */
class CuatrimestreFactory extends Factory
{
    protected $model = Cuatrimestre::class;

    public function definition(): array
    {
        return [
            'cuatrimestre' => 'Cuatrimestre '.fake()->unique()->bothify('??-###'),
        ];
    }
}
