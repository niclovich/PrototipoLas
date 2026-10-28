<?php

namespace App\Http\Controllers\Panel\Finanzas;

use App\Exceptions\OperacionNoPermitida;
use App\Http\Controllers\Concerns\ConPantallaConfirmacion;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Panel\Alumnos\AlumnoController;
use App\Http\Requests\Finanzas\RegistrarPagoRequest;
use App\Models\Alumno;
use App\Models\FacturaComprobante;
use App\Models\MedioPago;
use App\Services\Documentos\PdfService;
use App\Services\Finanzas\PagoCuotasService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * CU7 Registrar Pago de Cuotas (Cajero): P2 Registrar Pago de Cuotas (desde el "$" de P1 Gestión de Alumnos),
 * P3 Confirmación y el Comprobante de Pago en PDF.
 */
class PagoCuotaController extends Controller
{
    use ConPantallaConfirmacion;

    public const ENCABEZADO = 'REGISTRAR PAGO DE CUOTAS';

    public function __construct(private readonly PagoCuotasService $pagos) {}

    /** Pasos 2–17. */
    public function create(Request $request, Alumno $alumno): View|RedirectResponse
    {
        try {
            $pendientes = $this->pagos->iniciar($alumno, $request->user());
        } catch (OperacionNoPermitida $aviso) {
            return redirect()->route('panel.alumnos.index')->with(AlumnoController::AVISO, $aviso->getMessage());
        }

        $beca = $this->pagos->becaVigente($alumno);

        return view('panel.finanzas.pagos.registrar', [
            'alumno' => $alumno->load(['usuario', 'carrera']),
            'beca' => $beca,
            'pagadas' => $this->pagos->cuotasPagadas($alumno),
            'pendientes' => $pendientes,
            'vencimientos' => $pendientes->mapWithKeys(fn ($c) => [$c->id => $this->pagos->vencimiento($c)]),
            'medios' => MedioPago::query()->orderBy('id')->pluck('medio_pago', 'id')->all(),
        ]);
    }

    /** Pasos 18–23. */
    public function store(RegistrarPagoRequest $request, Alumno $alumno): RedirectResponse
    {
        try {
            $pago = $this->pagos->registrar(
                $alumno,
                $request->user(),
                array_map('intval', $request->validated('cuotas')),
                (int) $request->validated('medio_pago_id'),
                (float) $request->validated('importe'),
            );
        } catch (OperacionNoPermitida $aviso) {
            return redirect()->route('panel.alumnos.index')->with(AlumnoController::AVISO, $aviso->getMessage());
        }

        return $this->confirmar('panel.pagos.confirmacion', ['factura_id' => $pago->factura->id]);
    }

    /** P3: "Descargar Comprobante" y "Aceptar" (vuelve a P1 Gestión de Alumnos). */
    public function confirmacion(Request $request): View|RedirectResponse
    {
        $factura = FacturaComprobante::query()->find($this->datosConfirmacion($request)['factura_id'] ?? null);

        if (! $factura) {
            return redirect()->route('panel.alumnos.index');
        }

        return view('panel.finanzas.pagos.confirmacion', ['factura' => $factura]);
    }

    /** Comprobante de Pago (Fig. 88): Original y Duplicado, con QR a la validación pública (§7-6). */
    public function comprobante(FacturaComprobante $factura, PdfService $pdf): Response
    {
        $factura->load(['pago.alumno.usuario', 'pago.alumno.carrera', 'pago.detalles.cuota.mes']);

        return $pdf->generar('pdf.comprobante-pago', ['factura' => $factura], route('validar.comprobante', $factura->uuid))
            ->setPaper('a4', 'landscape')
            ->download('comprobante-'.$factura->numeroFormateado().'.pdf');
    }
}
