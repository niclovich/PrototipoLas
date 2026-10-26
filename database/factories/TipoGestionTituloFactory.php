<?php

namespace Database\Factories;

use App\Models\TipoGestionTitulo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TipoGestionTitulo>
 */
class TipoGestionTituloFactory extends Factory
{
    protected $model = TipoGestionTitulo::class;

    public function definition(): array
    {
        return [
            'descripcion' => 'Gestión '.fake()->unique()->bothify('??-###'),
        ];
    }
}
