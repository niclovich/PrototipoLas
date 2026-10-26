<?php

namespace Database\Factories;

use App\Enums\EstadoTramite;
use App\Models\Alumno;
use App\Models\TituloTramite;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TituloTramite>
 */
class TituloTramiteFactory extends Factory
{
    protected $model = TituloTramite::class;

    public function definition(): array
    {
        return [
            'alumno_id' => Alumno::factory(),
            'fecha_creacion' => today(),
            'estado_tramite' => EstadoTramite::SinGestion,
        ];
    }
}
