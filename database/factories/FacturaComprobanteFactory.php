<?php

namespace Database\Factories;

use App\Enums\EstadoGeneral;
use App\Models\FacturaComprobante;
use App\Models\Pago;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FacturaComprobante>
 */
class FacturaComprobanteFactory extends Factory
{
    protected $model = FacturaComprobante::class;

    public function definition(): array
    {
        return [
            'pago_id' => Pago::factory(),
            'numero_comprobante' => fake()->unique()->numberBetween(1, 99999999),
            'fecha_emision' => today(),
            'estado_factura' => EstadoGeneral::Activo,
        ];
    }
}
