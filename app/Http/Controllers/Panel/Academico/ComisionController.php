<?php

namespace App\Http\Controllers\Panel\Academico;

use App\DataTables\ComisionDataTable;
use App\Enums\EstadoCarrera;
use App\Http\Controllers\Concerns\ConPantallaConfirmacion;
use App\Http\Controllers\Controller;
use App\Http\Requests\Academico\RegistrarComisionRequest;
use App\Models\Carrera;
use App\Models\Comision;
use App\Services\Academico\ComisionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Gestión de Comisiones (Coordinador): P1, P2 Registrar Comisión y P3 Confirmación (CU 17).
 */
class ComisionController extends Controller
{
    use ConPantallaConfirmacion;

    public const ENCABEZADO = 'GESTIÓN DE COMISIONES';

    public function __construct(private readonly ComisionService $comisiones) {}

    /** P1: "Seleccionar Carrera" (carreras "Activo"; sin valor, la primera) y sus comisiones. */
    public function index(Request $request): View
    {
        $carreras = $this->comisiones->carreras();
        $id = $request->integer('carrera') ?: null;
        $carrera = $id === null ? $carreras->first() : ($carreras->firstWhere('id', $id) ?? abort(404));

        return view('panel.academico.comisiones.index', [
            'carreras' => $carreras->pluck('nombre_carrera', 'id')->all(),
            'carrera' => $carrera,
        ]);
    }

    public function datatable(Request $request, ComisionDataTable $tabla): JsonResponse
    {
        return $tabla->respuesta($request);
    }

    /** CU 17 pasos 1–24: P2 con la carrera elegida en P1. */
    public function create(Carrera $carrera): View
    {
        $this->asegurarActiva($carrera);
        $anios = range(1, $this->comisiones->duracion($carrera));

        return view('panel.academico.comisiones.registrar', [
            'carrera' => $carrera,
            'modalidades' => $this->comisiones->modalidades()->pluck('modalidad', 'id')->all(),
            'anios' => array_combine($anios, $anios),
            'cargas' => $this->comisiones->cargasPorAnio($carrera),
        ]);
    }

    /** CU 17 pasos 25–28. */
    public function store(RegistrarComisionRequest $request, Carrera $carrera): RedirectResponse
    {
        $this->asegurarActiva($carrera);
        $comision = $this->comisiones->registrar($carrera, $request->validated());

        return $this->confirmar('panel.comisiones.confirmacion', ['comision_id' => $comision->id]);
    }

    /** P3 Confirmación: "Aceptar" vuelve a P1 con la carrera. */
    public function confirmacion(Request $request): View|RedirectResponse
    {
        $datos = $this->datosConfirmacion($request);
        $comision = $datos ? Comision::find($datos['comision_id'] ?? null) : null;

        if (! $comision) {
            return redirect()->route('panel.comisiones.index');
        }

        return view('panel._confirmacion', [
            'encabezado' => self::ENCABEZADO,
            'titulo' => ComisionService::CONFIRMACION_REGISTRO,
            'lineas' => $this->comisiones->lineasConfirmacion($comision),
            'urlAceptar' => route('panel.comisiones.index', ['carrera' => $comision->carrera_id]),
        ]);
    }

    /** E9-D6: solo carreras "Activo" (spec paso 2). */
    private function asegurarActiva(Carrera $carrera): void
    {
        abort_unless($carrera->estado_carrera === EstadoCarrera::Activo, 404);
    }
}
