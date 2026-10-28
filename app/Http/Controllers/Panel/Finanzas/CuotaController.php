<?php

namespace App\Http\Controllers\Panel\Finanzas;

use App\DataTables\CuotaDataTable;
use App\Exceptions\OperacionNoPermitida;
use App\Http\Controllers\Concerns\ConPantallaConfirmacion;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finanzas\ModificarCuotaRequest;
use App\Http\Requests\Finanzas\RegistrarCuotaRequest;
use App\Models\Carrera;
use App\Models\Cuota;
use App\Services\Finanzas\CuotaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Gestión de Cuotas (Director General / Coordinador): P1, P2 Registrar (CU 33), P2 Modificar (CU 34) y P3 Confirmación.
 */
class CuotaController extends Controller
{
    use ConPantallaConfirmacion;

    public const ENCABEZADO = 'GESTIÓN DE CUOTAS';

    /** Clave de sesión del aviso que P1 muestra en un modal (CU 34 alt. 7.1 y 11.1). */
    public const AVISO = 'aviso_cuota';

    public function __construct(private readonly CuotaService $cuotas) {}

    /** P1: "Seleccionar Carrera" (sin valor, la primera) y "Año Lectivo" (sin valor, el actual). */
    public function index(Request $request): View
    {
        $carreras = $this->cuotas->carreras();
        $id = $request->integer('carrera') ?: null;
        $carrera = $id === null ? $carreras->first() : ($carreras->firstWhere('id', $id) ?? abort(404));
        $anios = $this->cuotas->aniosLectivos($carrera);
        $anio = $request->integer('anio') ?: CuotaService::anioActual();

        return view('panel.finanzas.cuotas.index', [
            'carreras' => $carreras->pluck('nombre_carrera', 'id')->all(),
            'carrera' => $carrera,
            'anios' => array_combine($anios, $anios),
            'anio' => in_array($anio, $anios, true) ? $anio : CuotaService::anioActual(),
        ]);
    }

    public function datatable(Request $request, CuotaDataTable $tabla): JsonResponse
    {
        return $tabla->respuesta($request);
    }

    /** CU 33 pasos 1–15: P2 Registrar Cuota con la carrera (y el año) elegidos en P1. */
    public function create(Request $request, Carrera $carrera): View
    {
        $this->asegurarGestionable($carrera);

        return view('panel.finanzas.cuotas.registrar', [
            'carrera' => $carrera,
            'meses' => $this->cuotas->meses(),
            'anio' => $request->integer('anio') ?: CuotaService::anioActual(),
        ]);
    }

    /** CU 33 pasos 16–18. */
    public function store(RegistrarCuotaRequest $request, Carrera $carrera): RedirectResponse
    {
        $this->asegurarGestionable($carrera);
        $cuota = $this->cuotas->registrar($carrera, $request->validated());

        return $this->confirmar('panel.cuotas.confirmacion', [
            'titulo' => CuotaService::CONFIRMACION_REGISTRO,
            'cuota_id' => $cuota->id,
            'anterior' => null,
        ]);
    }

    /** CU 34 pasos 10–13: valida año y pagos (alt. 7.1 / 11.1 → aviso en P1). */
    public function edit(Cuota $cuota): View|RedirectResponse
    {
        try {
            $this->cuotas->asegurarModificable($cuota);
        } catch (OperacionNoPermitida $aviso) {
            return $this->volverAP1($cuota, $aviso->getMessage());
        }

        return view('panel.finanzas.cuotas.modificar', ['cuota' => $cuota->load(['carrera', 'mes'])]);
    }

    /** CU 34 pasos 14–20. */
    public function update(ModificarCuotaRequest $request, Cuota $cuota): RedirectResponse
    {
        try {
            $anterior = $this->cuotas->modificar($cuota, $request->validated('monto'));
        } catch (OperacionNoPermitida $aviso) {
            return $this->volverAP1($cuota, $aviso->getMessage());
        }

        return $this->confirmar('panel.cuotas.confirmacion', [
            'titulo' => CuotaService::CONFIRMACION_MODIFICACION,
            'cuota_id' => $cuota->id,
            'anterior' => $anterior,
        ]);
    }

    /** P3 Confirmación: "Aceptar" vuelve a P1 con la carrera y el año de la cuota. */
    public function confirmacion(Request $request): View|RedirectResponse
    {
        $datos = $this->datosConfirmacion($request);
        $cuota = $datos ? Cuota::find($datos['cuota_id'] ?? null) : null;

        if (! $cuota) {
            return redirect()->route('panel.cuotas.index');
        }

        return view('panel._confirmacion', [
            'encabezado' => self::ENCABEZADO,
            'titulo' => $datos['titulo'],
            'lineas' => $this->cuotas->lineasConfirmacion($cuota, $datos['anterior'] ?? null),
            'urlAceptar' => route('panel.cuotas.index', ['carrera' => $cuota->carrera_id, 'anio' => $cuota->anio]),
        ]);
    }

    private function volverAP1(Cuota $cuota, string $mensaje): RedirectResponse
    {
        return redirect()->route('panel.cuotas.index', ['carrera' => $cuota->carrera_id, 'anio' => $cuota->anio])
            ->with(self::AVISO, $mensaje);
    }

    /** Paso 2: carreras "Pendiente" o "Activo". */
    private function asegurarGestionable(Carrera $carrera): void
    {
        abort_unless(Carrera::query()->gestionablesEnPlan()->whereKey($carrera->id)->exists(), 404);
    }
}
