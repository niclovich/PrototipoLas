<?php

namespace Database\Factories;

use App\Models\TipoDocumento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TipoDocumento>
 */
class TipoDocumentoFactory extends Factory
{
    protected $model = TipoDocumento::class;

    public function definition(): array
    {
        return [
            'descripcion' => 'Documento '.fake()->unique()->bothify('??-###'),
        ];
    }
}
