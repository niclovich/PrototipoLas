<?php

namespace Database\Factories;

use App\Enums\EstadoCarrera;
use App\Models\Carrera;
use App\Models\Duracion;
use App\Models\Modalidad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Carrera>
 */
class CarreraFactory extends Factory
{
    protected $model = Carrera::class;

    public function definition(): array
    {
        return [
            'nombre_carrera' => 'Tecnicatura en '.fake()->unique()->bothify('?????? ###'),
            'resolucion_carrera' => fake()->numerify('####/20##'),
            'estado_carrera' => EstadoCarrera::Activo,
            'modalidad_id' => Modalidad::factory(),
            'duracion_id' => Duracion::factory(),
        ];
    }

    /** Estado inicial de CU 3. */
    public function pendiente(): static
    {
        return $this->state(fn () => ['estado_carrera' => EstadoCarrera::Pendiente]);
    }

    public function inactiva(): static
    {
        return $this->state(fn () => ['estado_carrera' => EstadoCarrera::Inactivo]);
    }
}
