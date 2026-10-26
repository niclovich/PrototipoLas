<?php

namespace Database\Factories;

use App\Models\RolTribunal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RolTribunal>
 */
class RolTribunalFactory extends Factory
{
    protected $model = RolTribunal::class;

    public function definition(): array
    {
        return [
            'descripcion' => 'Rol '.fake()->unique()->bothify('??-###'),
        ];
    }
}
