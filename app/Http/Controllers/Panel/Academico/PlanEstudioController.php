<?php

namespace App\Http\Controllers\Panel\Academico;

use App\DataTables\DetallePlanEstudioDataTable;
use App\Exceptions\OperacionNoPermitida;
use App\Http\Controllers\Concerns\ConPantallaConfirmacion;
use App\Http\Controllers\Controller;
use App\Http\Requests\Academico\RegistrarPlanEstudioRequest;
use App\Models\Carrera;
use App\Services\Academico\PlanEstudioService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Gestión de Plan de Estudio (Coordinador): P1 / P1c (entrada de CU 5–8), CU 5 P2 Registrar Plan y la
 * P3 Confirmación común de CU 5, 6, 7 y 8.
 */
class PlanEstudioController extends Controller
{
    use ConPantallaConfirmacion;

    public const ENCABEZADO = 'GESTIÓN DE PLAN DE ESTUDIO';

    /** Clave de sesión del aviso que P1 muestra en un modal (CU 7 alt. 6). */
    public const AVISO = 'aviso_plan';

    public function __construct(private readonly PlanEstudioService $planes) {}

    /** P1 (con plan activo) o P1c (sin plan activo) según la carrera elegida. */
    public function index(Request $request): View
    {
        $carreras = $this->planes->carrerasSeleccionables();
        $carrera = $this->planes->carreraElegida($carreras, $request->integer('carrera') ?: null);
        $planActivo = $carrera?->loadMissing('planActivo')->planActivo;
        $plan = $planActivo ? $this->planes->planElegido($carrera, $request->integer('plan') ?: null) : null;

        return view('panel.academico.planes.index', [
            'carreras' => $carreras->pluck('nombre_carrera', 'id')->all(),
            'carrera' => $carrera,
            'plan' => $plan,
            'planes' => $plan ? $this->planes->planesDe($carrera)->pluck('anio_plan_estudio', 'id')->all() : [],
            'puedeRegistrarPlan' => $carrera && $this->planes->puedeRegistrarPlan($carrera),
        ]);
    }

    public function datatable(Request $request, DetallePlanEstudioDataTable $tabla): JsonResponse
    {
        return $tabla->respuesta($request);
    }

    /** CU 5 pasos 5–6: P2 Registrar Plan de Estudio. */
    public function create(Carrera $carrera): View|RedirectResponse
    {
        if (! $this->planes->puedeRegistrarPlan($carrera)) {
            return $this->volverAP1($carrera->id);
        }

        return view('panel.academico.planes.registrar', ['carrera' => $carrera]);
    }

    /** CU 5 pasos 7–13. */
    public function store(RegistrarPlanEstudioRequest $request, Carrera $carrera): RedirectResponse
    {
        try {
            $plan = $this->planes->registrar($carrera, (int) $request->validated('anio_plan_estudio'));
        } catch (OperacionNoPermitida) {
            return $this->volverAP1($carrera->id);
        }

        return $this->confirmar('panel.planes.confirmacion', [
            'titulo' => PlanEstudioService::CONFIRMACION_REGISTRO,
            'lineas' => $this->planes->lineasConfirmacion($plan),
            'carrera_id' => $carrera->id,
        ]);
    }

    /** P3 Confirmación de CU 5–8: "Aceptar" vuelve a P1 con la misma carrera. */
    public function confirmacion(Request $request): View|RedirectResponse
    {
        $datos = $this->datosConfirmacion($request);

        if (! isset($datos['titulo'], $datos['lineas'], $datos['carrera_id'])) {
            return redirect()->route('panel.planes.index');
        }

        return view('panel._confirmacion', [
            'encabezado' => self::ENCABEZADO,
            'titulo' => $datos['titulo'],
            'lineas' => $datos['lineas'],
            'urlAceptar' => route('panel.planes.index', ['carrera' => $datos['carrera_id']]),
        ]);
    }

    private function volverAP1(int $carreraId): RedirectResponse
    {
        return redirect()->route('panel.planes.index', ['carrera' => $carreraId]);
    }
}
