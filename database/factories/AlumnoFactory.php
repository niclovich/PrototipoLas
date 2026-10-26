<?php

namespace Database\Factories;

use App\Enums\EstadoAlumno;
use App\Enums\SituacionEstudios;
use App\Models\Alumno;
use App\Models\Carrera;
use App\Models\PlanEstudio;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Alumno>
 */
class AlumnoFactory extends Factory
{
    protected $model = Alumno::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'carrera_id' => Carrera::factory(),
            'plan_estudio_id' => fn (array $a) => PlanEstudio::factory()->create(['carrera_id' => $a['carrera_id']])->id,
            'estado_alumno' => EstadoAlumno::Preinscripto,
            'situacion_estudios' => SituacionEstudios::EnCurso,
            'adeuda_materia' => false,
        ];
    }
}
