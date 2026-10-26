<?php

namespace Database\Factories;

use App\Models\Llamado;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Llamado>
 */
class LlamadoFactory extends Factory
{
    protected $model = Llamado::class;

    public function definition(): array
    {
        return [
            'llamado' => 'Llamado '.fake()->unique()->bothify('??-###'),
        ];
    }
}
