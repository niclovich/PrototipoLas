<?php

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Models\FacturaComprobante;
use Illuminate\Contracts\View\View;

/** Validación pública del Comprobante de Pago de CU7 (QR del PDF, decisión §7-6). Muestra solo datos mínimos. */
class ValidarComprobanteController extends Controller
{
    public function __invoke(string $uuid): View
    {
        $factura = FacturaComprobante::query()->where('uuid', $uuid)->with(['pago.alumno.usuario', 'pago.alumno.carrera'])->firstOrFail();

        return view('validacion.comprobante', ['factura' => $factura]);
    }
}
