<?php

namespace Database\Factories;

use App\Models\Caja;
use App\Models\DetalleCierreCaja;
use App\Models\MedioPago;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DetalleCierreCaja>
 */
class DetalleCierreCajaFactory extends Factory
{
    protected $model = DetalleCierreCaja::class;

    public function definition(): array
    {
        return [
            'caja_id' => Caja::factory(),
            'medio_pago_id' => MedioPago::factory(),
            'monto_esperado' => 50000,
            'monto_contado' => 50000,
            'diferencia' => 0,
        ];
    }
}
