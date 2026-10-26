<?php

namespace Database\Factories;

use App\Enums\EstadoDetalleActa;
use App\Models\ActaExamen;
use App\Models\Alumno;
use App\Models\DetalleActa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DetalleActa>
 */
class DetalleActaFactory extends Factory
{
    protected $model = DetalleActa::class;

    public function definition(): array
    {
        return [
            'acta_examen_id' => ActaExamen::factory(),
            'alumno_id' => Alumno::factory(),
            'estado_detalle_acta' => EstadoDetalleActa::Activo,
        ];
    }
}
