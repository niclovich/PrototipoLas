<?php

namespace Database\Factories;

use App\Models\Localidad;
use App\Models\Provincia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Localidad>
 */
class LocalidadFactory extends Factory
{
    protected $model = Localidad::class;

    public function definition(): array
    {
        return [
            'provincia_id' => Provincia::factory(),
            'localidad' => 'Localidad '.fake()->unique()->bothify('??-####'),
        ];
    }
}
