<?php

namespace Database\Factories;

use App\Models\Mes;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Mes>
 */
class MesFactory extends Factory
{
    protected $model = Mes::class;

    /** Toma un Mes de los valores precargados que todavía no exista en la base. */
    public function definition(): array
    {
        return [
            'descripcion' => fake()->unique()->randomElement(array_slice(array_keys(Mes::VALORES), 1)),
        ];
    }

    public function inscripcion(): static
    {
        return $this->state(['descripcion' => Mes::INSCRIPCION]);
    }
}
