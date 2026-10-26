<?php

namespace Database\Factories;

use App\Enums\EstadoAlumnoComision;
use App\Enums\TipoInscripcion;
use App\Models\Alumno;
use App\Models\AlumnoComision;
use App\Models\DetComision;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AlumnoComision>
 */
class AlumnoComisionFactory extends Factory
{
    protected $model = AlumnoComision::class;

    public function definition(): array
    {
        return [
            'alumno_id' => Alumno::factory(),
            'det_comision_id' => DetComision::factory(),
            'fecha_inscripcion' => today(),
            'estado_alumno_comision' => EstadoAlumnoComision::Activo,
            'tipo_inscripcion' => TipoInscripcion::PorComision,
        ];
    }
}
