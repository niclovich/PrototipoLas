<?php

namespace Database\Factories;

use App\Models\Modalidad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Modalidad>
 */
class ModalidadFactory extends Factory
{
    protected $model = Modalidad::class;

    public function definition(): array
    {
        return [
            'modalidad' => 'Modalidad '.fake()->unique()->bothify('??-###'),
        ];
    }
}
