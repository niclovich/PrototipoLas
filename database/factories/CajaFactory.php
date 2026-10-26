<?php

namespace Database\Factories;

use App\Enums\EstadoCaja;
use App\Models\Caja;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Caja>
 */
class CajaFactory extends Factory
{
    protected $model = Caja::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'fecha_apertura' => now(),
            'monto_inicial' => 10000,
            'estado_caja' => EstadoCaja::Abierta,
        ];
    }
}
