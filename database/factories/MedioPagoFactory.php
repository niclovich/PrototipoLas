<?php

namespace Database\Factories;

use App\Models\MedioPago;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MedioPago>
 */
class MedioPagoFactory extends Factory
{
    protected $model = MedioPago::class;

    public function definition(): array
    {
        return [
            'medio_pago' => 'Medio '.fake()->unique()->bothify('??-###'),
        ];
    }
}
