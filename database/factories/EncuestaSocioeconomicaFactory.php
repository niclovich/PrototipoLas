<?php

namespace Database\Factories;

use App\Models\EncuestaSocioeconomica;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EncuestaSocioeconomica>
 */
class EncuestaSocioeconomicaFactory extends Factory
{
    protected $model = EncuestaSocioeconomica::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'trabaja' => fake()->boolean(),
            'sabe_usar_pc' => true,
            'es_usuario_internet' => true,
            'nivel_estudios_madre' => 'Secundario completo',
            'nivel_estudios_padre' => 'Secundario completo',
            'idiomas' => 'Español',
        ];
    }
}
