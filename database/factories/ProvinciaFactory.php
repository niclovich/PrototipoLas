<?php

namespace Database\Factories;

use App\Models\Provincia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Provincia>
 */
class ProvinciaFactory extends Factory
{
    protected $model = Provincia::class;

    public function definition(): array
    {
        return [
            'provincia' => 'Provincia '.fake()->unique()->bothify('??-####'),
        ];
    }
}
