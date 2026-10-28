<?php

namespace App\Enums;

use App\Enums\Concerns\EnumConEtiqueta;

/**
 * "Idiomas que habla" de la Encuesta (CU1 P3, multiselección; decisión D3 de E3). Lista editable.
 */
enum Idioma: string
{
    use EnumConEtiqueta;

    case Espanol = 'Español';
    case Guarani = 'Guaraní';
    case Quechua = 'Quechua';
    case Wichi = 'Wichí';
    case Ingles = 'Inglés';
    case Portugues = 'Portugués';
    case Otro = 'Otro';

    public function badge(): string
    {
        return 'secondary';
    }
}
