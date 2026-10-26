<?php

namespace Database\Factories;

use App\Enums\EstadoGeneral;
use App\Models\DocumentoPresentado;
use App\Models\TipoDocumento;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentoPresentado>
 */
class DocumentoPresentadoFactory extends Factory
{
    protected $model = DocumentoPresentado::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'tipo_documento_id' => TipoDocumento::factory(),
            'nombre_documento' => 'documento.pdf',
            'fecha_presentacion' => today(),
            'archivo_documento' => 'documentos/'.fake()->uuid().'.pdf',
            'estado_documento' => EstadoGeneral::Activo,
        ];
    }
}
