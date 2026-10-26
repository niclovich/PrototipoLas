<?php

namespace Database\Factories;

use App\Models\Motivo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Motivo>
 */
class MotivoFactory extends Factory
{
    protected $model = Motivo::class;

    public function definition(): array
    {
        return [
            'descripcion' => 'Motivo '.fake()->unique()->bothify('??-###'),
            'accion' => Motivo::ACCION_BAJA_PERSONAL,
        ];
    }
}
