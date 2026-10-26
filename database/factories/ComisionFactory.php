<?php

namespace Database\Factories;

use App\Enums\EstadoGeneral;
use App\Models\Carrera;
use App\Models\Comision;
use App\Models\Modalidad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comision>
 */
class ComisionFactory extends Factory
{
    protected $model = Comision::class;

    public function definition(): array
    {
        return [
            'carrera_id' => Carrera::factory(),
            'modalidad_id' => Modalidad::factory(),
            'codigo_comision' => fake()->unique()->bothify('C-###'),
            'capacidad_comision' => 40,
            'carga_horaria_comision' => 20,
            'horario_comision' => 'Lunes a viernes 18:00 a 22:00',
            'anio_curso' => 1,
            'anio_lectivo' => (int) now()->format('Y'),
            'estado_comision' => EstadoGeneral::Activo,
        ];
    }
}
