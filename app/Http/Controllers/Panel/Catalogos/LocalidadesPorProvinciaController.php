<?php

namespace App\Http\Controllers\Panel\Catalogos;

use App\Http\Controllers\Controller;
use App\Models\Provincia;
use Illuminate\Http\JsonResponse;

/**
 * Localidades (sin las dadas de baja) de una Provincia, para los selects dependientes
 * (<x-select-provincia-localidad>). Cada módulo lo monta en su grupo con su permiso.
 */
class LocalidadesPorProvinciaController extends Controller
{
    public function __invoke(Provincia $provincia): JsonResponse
    {
        return response()->json(
            $provincia->localidades()->orderBy('localidad')->get(['id', 'localidad'])
        );
    }
}
