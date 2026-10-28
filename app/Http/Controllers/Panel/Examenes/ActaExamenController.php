<?php

namespace App\Http\Controllers\Panel\Examenes;

use App\DataTables\ActaExamenDataTable;
use App\Enums\EstadoCarrera;
use App\Exceptions\OperacionNoPermitida;
use App\Http\Controllers\Concerns\ConPantallaConfirmacion;
use App\Http\Controllers\Controller;
use App\Http\Requests\Examenes\ModificarActaExamenRequest;
use App\Http\Requests\Examenes\RegistrarActaExamenRequest;
use App\Models\ActaExamen;
use App\Models\Carrera;
use App\Services\Examenes\ActaExamenService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Gestión de Actas Examen (Coordinador): P1, P2 Registrar (CU 21), P2 Modificar (CU 22) y P3 Confirmación.
 */
class ActaExamenController extends Controller
{
    use ConPantallaConfirmacion;

    public const ENCABEZADO = 'GESTIÓN DE ACTAS EXAMEN';

    /** Clave de sesión del aviso que P1 muestra en un modal (CU 22 alt. 8.1). */
    public const AVISO = 'aviso_acta';

    /** Título del modal de aviso de P1 (por defecto, el de CU 22 alt. 8.1). */
    public const AVISO_TITULO = 'aviso_acta_titulo';

    public function __construct(private readonly ActaExamenService $actas) {}

    /** P1: "Seleccionar Carrera" (carreras "Activo"; sin valor, la primera), "Buscar.." y "Asentada". */
    public function index(Request $request): View
    {
        $carreras = $this->actas->carreras();
        $id = $request->integer('carrera') ?: null;
        $carrera = $id === null ? $carreras->first() : ($carreras->firstWhere('id', $id) ?? abort(404));

        return view('panel.examenes.actas.index', [
            'carreras' => $carreras->pluck('nombre_carrera', 'id')->all(),
            'carrera' => $carrera,
        ]);
    }

    public function datatable(Request $request, ActaExamenDataTable $tabla): JsonResponse
    {
        return $tabla->respuesta($request);
    }

    /** CU 21 pasos 1–27: P2 Registrar con la carrera elegida en P1. */
    public function create(Carrera $carrera): View
    {
        abort_unless($carrera->estado_carrera === EstadoCarrera::Activo, 404);

        return view('panel.examenes.actas.registrar', [
            'carrera' => $carrera,
            'materias' => $this->actas->materias($carrera),
            ...$this->actas->opciones(),
        ]);
    }

    /** CU 21 pasos 28–33. */
    public function store(RegistrarActaExamenRequest $request, Carrera $carrera): RedirectResponse
    {
        abort_unless($carrera->estado_carrera === EstadoCarrera::Activo, 404);
        $acta = $this->actas->registrar($request->datos());

        return $this->confirmar('panel.actas.confirmacion', [
            'titulo' => ActaExamenService::CONFIRMACION_REGISTRO,
            'mensaje' => ActaExamenService::CONFIRMACION_REGISTRO_TEXTO,
            'acta_id' => $acta->id,
        ]);
    }

    /** CU 22 pasos 7–27: alt. 8.1 → aviso en P1. */
    public function edit(ActaExamen $acta): View|RedirectResponse
    {
        try {
            $this->actas->asegurarModificable($acta);
        } catch (OperacionNoPermitida $aviso) {
            return $this->volverAP1($acta, $aviso->getMessage());
        }

        return view('panel.examenes.actas.modificar', [
            'acta' => $acta->load(['detallePlan.materia', 'detallePlan.planEstudio.carrera']),
            'tribunal' => $this->actas->tribunalActual($acta),
            ...$this->actas->opciones(),
        ]);
    }

    /** CU 22 pasos 28–34. */
    public function update(ModificarActaExamenRequest $request, ActaExamen $acta): RedirectResponse
    {
        try {
            $acta = $this->actas->modificar($acta, $request->datos());
        } catch (OperacionNoPermitida $aviso) {
            return $this->volverAP1($acta, $aviso->getMessage());
        }

        return $this->confirmar('panel.actas.confirmacion', [
            'titulo' => ActaExamenService::CONFIRMACION_MODIFICACION,
            'mensaje' => null,
            'acta_id' => $acta->id,
        ]);
    }

    /** P3 Confirmación (P3.png): "Aceptar" vuelve a P1 con la carrera del acta. */
    public function confirmacion(Request $request): View|RedirectResponse
    {
        $datos = $this->datosConfirmacion($request);
        $acta = $datos ? ActaExamen::with('detallePlan.planEstudio')->find($datos['acta_id'] ?? null) : null;

        if (! $acta) {
            return redirect()->route('panel.actas.index');
        }

        return view('panel._confirmacion', [
            'encabezado' => self::ENCABEZADO,
            'titulo' => $datos['titulo'],
            'mensaje' => $datos['mensaje'] ?? null,
            'tituloLineas' => 'Detalle del Registro',
            'lineas' => $this->actas->lineasConfirmacion($acta),
            'urlAceptar' => route('panel.actas.index', ['carrera' => $acta->detallePlan->planEstudio->carrera_id]),
        ]);
    }

    private function volverAP1(ActaExamen $acta, string $mensaje): RedirectResponse
    {
        $carrera = $acta->loadMissing('detallePlan.planEstudio')->detallePlan->planEstudio->carrera_id;

        return redirect()->route('panel.actas.index', ['carrera' => $carrera])->with(self::AVISO, $mensaje);
    }
}
