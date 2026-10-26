<?php

namespace Database\Factories;

use App\Models\Duracion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Duracion>
 */
class DuracionFactory extends Factory
{
    protected $model = Duracion::class;

    public function definition(): array
    {
        return [
            'duracion' => '3 años '.fake()->unique()->bothify('??-###'),
            'cantidad_anios' => 3,
        ];
    }
}
