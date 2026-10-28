<?php

namespace App\Http\Controllers\Panel\Academico;

use App\Exceptions\OperacionNoPermitida;
use App\Http\Controllers\Concerns\ConPantallaConfirmacion;
use App\Http\Controllers\Controller;
use App\Http\Requests\Academico\RegistrarMateriaPlanRequest;
use App\Models\DetallePlanEstudio;
use App\Models\PlanEstudio;
use App\Services\Academico\DetallePlanEstudioService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * CU 6 Registrar Materia al Plan de Estudio (P2 → P3) y CU 7 Registrar Baja de Materia del Plan (modal sobre P1 → P3).
 * {plan}: plan "Activo" de una carrera gestionable; {detalle}: detalle "Activo" de ese tipo de plan (routes/panel.php).
 */
class DetallePlanEstudioController extends Controller
{
    use ConPantallaConfirmacion;

    public function __construct(private readonly DetallePlanEstudioService $detalles) {}

    /** CU 6 pasos 5–23: P2 Registrar Materia al Plan. */
    public function create(PlanEstudio $plan): View
    {
        $plan->load('carrera.duracion');

        return view('panel.academico.planes.materias.registrar', [
            'plan' => $plan,
            'materias' => $this->detalles->materiasSeleccionables(),
            'regimenes' => $this->detalles->regimenes(),
            'cuatrimestres' => $this->detalles->cuatrimestres(),
            'anios' => $this->detalles->aniosDeDictado($plan),
        ]);
    }

    /** CU 6 pasos 24–28. */
    public function store(RegistrarMateriaPlanRequest $request, PlanEstudio $plan): RedirectResponse
    {
        $detalle = $this->detalles->registrar($plan, $request->validated());

        return $this->confirmar('panel.planes.confirmacion', [
            'titulo' => DetallePlanEstudioService::CONFIRMACION_REGISTRO,
            'lineas' => $this->detalles->lineasRegistro($detalle),
            'carrera_id' => $plan->carrera_id,
        ]);
    }

    /** CU 7 pasos 6–10. Con comisiones dictándose: aviso en P1 y fin del CU (alt. 6). */
    public function registrarBaja(DetallePlanEstudio $detalle): RedirectResponse
    {
        $carreraId = $detalle->planEstudio->carrera_id;

        try {
            $detalle = $this->detalles->registrarBaja($detalle);
        } catch (OperacionNoPermitida $aviso) {
            return redirect()->route('panel.planes.index', ['carrera' => $carreraId])
                ->with(PlanEstudioController::AVISO, $aviso->getMessage());
        }

        return $this->confirmar('panel.planes.confirmacion', [
            'titulo' => DetallePlanEstudioService::CONFIRMACION_BAJA,
            'lineas' => $this->detalles->lineasBaja($detalle),
            'carrera_id' => $carreraId,
        ]);
    }
}
