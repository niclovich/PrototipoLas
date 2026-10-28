<?php

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Models\DetalleActa;
use Illuminate\Contracts\View\View;

/** Validación pública del Comprobante de Inscripción a Examen de CU5 (QR del PDF, por DetalleActa.uuid). */
class ValidarInscripcionExamenController extends Controller
{
    public function __invoke(string $uuid): View
    {
        $detalle = DetalleActa::query()->where('uuid', $uuid)
            ->with(['alumno.usuario', 'acta.detallePlan.materia', 'acta.llamado'])->firstOrFail();

        return view('validacion.inscripcion-examen', ['detalle' => $detalle]);
    }
}
