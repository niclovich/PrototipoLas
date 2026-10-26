<?php

namespace Database\Factories;

use App\Enums\EstadoGeneral;
use App\Models\Alumno;
use App\Models\Caja;
use App\Models\MedioPago;
use App\Models\Pago;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pago>
 */
class PagoFactory extends Factory
{
    protected $model = Pago::class;

    public function definition(): array
    {
        return [
            'alumno_id' => Alumno::factory(),
            'caja_id' => Caja::factory(),
            'medio_pago_id' => MedioPago::factory(),
            'fecha_pago' => now(),
            'monto_total' => 50000,
            'estado_pago' => EstadoGeneral::Activo,
        ];
    }
}
