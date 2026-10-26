<?php

namespace Database\Factories;

use App\Models\Regimen;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Regimen>
 */
class RegimenFactory extends Factory
{
    protected $model = Regimen::class;

    public function definition(): array
    {
        return [
            'regimen' => 'Régimen '.fake()->unique()->bothify('??-###'),
        ];
    }
}
