<?php

namespace Database\Factories;

use App\Models\Motivo;
use App\Models\Novedad;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Novedad>
 */
class NovedadFactory extends Factory
{
    protected $model = Novedad::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'motivo_id' => Motivo::factory(),
            'fecha_novedad' => today(),
            'observacion_novedad' => fake()->sentence(),
        ];
    }

    /** Baja de Personal (CU 27): Motivo con la acción "Baja de Personal". */
    public function bajaDePersonal(): static
    {
        return $this->state(fn () => ['motivo_id' => Motivo::factory()->state(['accion' => Motivo::ACCION_BAJA_PERSONAL])]);
    }
}
