<?php

namespace Database\Factories;

use App\Enums\EstadoGeneral;
use App\Models\IntegranteTribunal;
use App\Models\RolTribunal;
use App\Models\Tribunal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IntegranteTribunal>
 */
class IntegranteTribunalFactory extends Factory
{
    protected $model = IntegranteTribunal::class;

    public function definition(): array
    {
        return [
            'tribunal_id' => Tribunal::factory(),
            'user_id' => User::factory(),
            'rol_tribunal_id' => RolTribunal::factory(),
            'estado_integrante' => EstadoGeneral::Activo,
        ];
    }
}
