<?php

namespace App\Http\Controllers\Panel\Finanzas;

use App\DataTables\CajaDataTable;
use App\Enums\EstadoCaja;
use App\Exceptions\OperacionNoPermitida;
use App\Http\Controllers\Concerns\ConPantallaConfirmacion;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finanzas\AbrirCajaRequest;
use App\Http\Requests\Finanzas\CerrarCajaRequest;
use App\Models\Caja;
use App\Services\Finanzas\CajaService;
use App\Support\Moneda;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Gestión de Cajas (Cajero): P1, P2 Apertura (CU 35), P2 Cierre desde "Ver" (CU 36) y P3 Confirmación.
 */
class CajaController extends Controller
{
    use ConPantallaConfirmacion;

    public const ENCABEZADO = 'GESTIÓN DE CAJAS';

    /** Clave de sesión del aviso que P1 muestra en un modal (CU 35 alt. 2.1, CU 36 alt. 2.1). */
    public const AVISO = 'aviso_caja';

    public function __construct(private readonly CajaService $cajas) {}

    /** P1: filtro "Estado" y "Abrir Caja" (habilitado solo sin caja abierta). */
    public function index(Request $request): View
    {
        return view('panel.finanzas.cajas.index', [
            'estados' => EstadoCaja::opciones(),
            'tieneCajaAbierta' => $this->cajas->cajaAbiertaDe($request->user()) !== null,
        ]);
    }

    public function datatable(Request $request, CajaDataTable $tabla): JsonResponse
    {
        return $tabla->respuesta($request);
    }

    /** CU 35 pasos 1–3. */
    public function create(Request $request): View|RedirectResponse
    {
        if ($this->cajas->cajaAbiertaDe($request->user())) {
            return $this->volverAP1(CajaService::MENSAJE_YA_TIENE_CAJA);
        }

        return view('panel.finanzas.cajas.apertura', ['fechaApertura' => now()]);
    }

    /** CU 35 pasos 4–10. */
    public function store(AbrirCajaRequest $request): RedirectResponse
    {
        try {
            $caja = $this->cajas->abrir($request->user(), (float) $request->validated('monto_inicial'));
        } catch (OperacionNoPermitida $aviso) {
            return $this->volverAP1($aviso->getMessage());
        }

        return $this->confirmar('panel.cajas.confirmacion', [
            'titulo' => CajaService::CONFIRMACION_APERTURA,
            'lineas' => ["Caja {$caja->id}", 'Fecha de Apertura: '.$caja->fecha_apertura->format('d/m/Y H:i:s'), 'Monto Inicial: '.Moneda::formatear($caja->monto_inicial)],
        ]);
    }

    /** "Ver": CU 36 pasos 1–5 si la caja está Abierta y es del cajero; si no, el detalle registrado en solo lectura (E13-D2). */
    public function show(Request $request, Caja $caja): View
    {
        return view('panel.finanzas.cajas.cierre', [
            'caja' => $caja->load('cajero'),
            'filas' => $this->cajas->detalleCierre($caja),
            'editable' => $this->cajas->puedeCerrar($caja, $request->user()) && $request->user()->can('editar_caja'),
        ]);
    }

    /** CU 36 pasos 6–19. */
    public function cerrar(CerrarCajaRequest $request, Caja $caja): RedirectResponse
    {
        try {
            $caja = $this->cajas->cerrar($caja, $request->user(), $request->validated('contados'), $request->validated('observacion_cierre'));
        } catch (OperacionNoPermitida $aviso) {
            return $this->volverAP1($aviso->getMessage());
        }

        return $this->confirmar('panel.cajas.confirmacion', [
            'titulo' => CajaService::CONFIRMACION_CIERRE,
            'lineas' => ["Caja {$caja->id}", 'Fecha de Cierre: '.$caja->fecha_cierre->format('d/m/Y H:i:s'), 'Diferencia Total: '.Moneda::formatear($caja->diferencia_total)],
        ]);
    }

    /** P3: "Aceptar" vuelve a P1. */
    public function confirmacion(Request $request): View|RedirectResponse
    {
        $datos = $this->datosConfirmacion($request);

        if (! $datos) {
            return redirect()->route('panel.cajas.index');
        }

        return view('panel._confirmacion', [
            'encabezado' => self::ENCABEZADO,
            'titulo' => $datos['titulo'],
            'lineas' => $datos['lineas'],
            'urlAceptar' => route('panel.cajas.index'),
        ]);
    }

    private function volverAP1(string $mensaje): RedirectResponse
    {
        return redirect()->route('panel.cajas.index')->with(self::AVISO, $mensaje);
    }
}
