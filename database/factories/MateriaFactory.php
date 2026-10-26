<?php

namespace Database\Factories;

use App\Enums\EstadoGeneral;
use App\Models\Materia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Materia>
 */
class MateriaFactory extends Factory
{
    protected $model = Materia::class;

    public function definition(): array
    {
        return [
            'nombre_materia' => 'Materia '.fake()->unique()->bothify('?????? ###'),
            'estado_materia' => EstadoGeneral::Activo,
        ];
    }
}
