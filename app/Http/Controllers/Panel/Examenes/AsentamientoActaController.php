<?php

namespace App\Http\Controllers\Panel\Examenes;

use App\Exceptions\OperacionNoPermitida;
use App\Http\Controllers\Concerns\ConPantallaConfirmacion;
use App\Http\Controllers\Controller;
use App\Http\Requests\Examenes\AsentarActaRequest;
use App\Http\Requests\Examenes\RegistrarNotaExamenRequest;
use App\Models\ActaExamen;
use App\Models\DetalleActa;
use App\Services\Examenes\AsentamientoActaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * CU6 Asentamiento de Acta (Preceptor): desde el 👁 de P1 Gestión de Actas Examen → P2 Alumnos Inscriptos al Acta
 * → P3 Formulario Registrar Nota del Alumno → (Asentar Acta) P4 Confirmación.
 */
class AsentamientoActaController extends Controller
{
    use ConPantallaConfirmacion;

    public const ENCABEZADO = 'ASENTAMIENTO DE ACTA DE EXAMEN';

    public const TITULO = 'Asentamiento de Acta';

    /** Aviso de P2 y P3 (alt. 10.1 / 16.1 van como error del campo). */
    public const AVISO = 'aviso_asentamiento';

    public function __construct(private readonly AsentamientoActaService $asentamiento) {}

    /** P2: pasos 4–6 y 14 (Nro de Folio + "Asentar Acta"). */
    public function show(ActaExamen $acta): View|RedirectResponse
    {
        try {
            $this->asentamiento->asegurarPendiente($acta);
        } catch (OperacionNoPermitida $aviso) {
            return $this->volverAGestion($acta, $aviso->getMessage());
        }

        return view('panel.examenes.asentamiento.show', [
            'acta' => $acta->load(['detallePlan.materia', 'modalidad', 'llamado']),
            'inscriptos' => $this->asentamiento->inscriptos($acta),
            'completa' => $this->asentamiento->completa($acta),
        ]);
    }

    /** P3: pasos 7–8. */
    public function edit(DetalleActa $detalleActa): View|RedirectResponse
    {
        $acta = $detalleActa->acta;
        try {
            $this->asentamiento->asegurarPendiente($acta);
        } catch (OperacionNoPermitida $aviso) {
            return $this->volverAGestion($acta, $aviso->getMessage());
        }

        return view('panel.examenes.asentamiento.nota', [
            'detalle' => $detalleActa->load(['alumno.usuario', 'acta.detallePlan.materia']),
        ]);
    }

    /** P3 "Guardar": pasos 9–13 (alt. 10.1 en el FormRequest) → vuelve a P2. */
    public function update(RegistrarNotaExamenRequest $request, DetalleActa $detalleActa): RedirectResponse
    {
        try {
            $this->asentamiento->registrarNota($detalleActa, (float) $request->validated('nota_acta'), $request->validated('observacion'));
        } catch (OperacionNoPermitida $aviso) {
            return $this->volverAGestion($detalleActa->acta, $aviso->getMessage());
        }

        return redirect()->route('panel.asentamiento.show', $detalleActa->acta_examen_id);
    }

    /** "Asentar Acta" (confirmación SweetAlert = pasos 17–19): pasos 20–25 (alt. 16.1 en el FormRequest). */
    public function store(AsentarActaRequest $request, ActaExamen $acta): RedirectResponse
    {
        try {
            $acta = $this->asentamiento->asentar($acta, (int) $request->validated('nro_folio'));
        } catch (OperacionNoPermitida $aviso) {
            return redirect()->route('panel.asentamiento.show', $acta)->with(self::AVISO, $aviso->getMessage());
        }

        return $this->confirmar('panel.asentamiento.confirmacion', ['acta_id' => $acta->id]);
    }

    /** P4 Confirmación: "Aceptar" → P1 Gestión de Actas con la carrera del acta. */
    public function confirmacion(Request $request): View|RedirectResponse
    {
        $acta = ActaExamen::query()->with('detallePlan.planEstudio')->find($this->datosConfirmacion($request)['acta_id'] ?? null);

        if (! $acta) {
            return redirect()->route('panel.actas.index');
        }

        return view('panel.examenes.asentamiento.confirmacion', [
            'acta' => $acta,
            'lineas' => $this->asentamiento->lineasConfirmacion($acta),
        ]);
    }

    private function volverAGestion(ActaExamen $acta, string $mensaje): RedirectResponse
    {
        return redirect()->route('panel.actas.index', ['carrera' => $acta->detallePlan?->planEstudio?->carrera_id])
            ->with(ActaExamenController::AVISO, $mensaje)->with(ActaExamenController::AVISO_TITULO, self::TITULO);
    }
}
