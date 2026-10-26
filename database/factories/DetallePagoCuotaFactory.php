<?php

namespace Database\Factories;

use App\Models\Cuota;
use App\Models\DetallePagoCuota;
use App\Models\Pago;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DetallePagoCuota>
 */
class DetallePagoCuotaFactory extends Factory
{
    protected $model = DetallePagoCuota::class;

    public function definition(): array
    {
        return [
            'pago_id' => Pago::factory(),
            'cuota_id' => Cuota::factory(),
            'monto_aplicado' => 50000,
            'monto_bonificado' => 0,
        ];
    }
}
