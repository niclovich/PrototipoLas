<?php

namespace App\Http\Controllers\Panel\Academico;

use App\DataTables\CarreraDataTable;
use App\Enums\EstadoCarrera;
use App\Http\Controllers\Concerns\ConPantallaConfirmacion;
use App\Http\Controllers\Controller;
use App\Http\Requests\Academico\ModificarCarreraRequest;
use App\Http\Requests\Academico\RegistrarCarreraRequest;
use App\Models\Carrera;
use App\Services\Academico\CarreraService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Gestión de Carreras (Coordinador): P1 (entrada de CU 3 y CU 4), P2 Registrar, P2 Modificar y P3 Confirmación.
 */
class CarreraController extends Controller
{
    use ConPantallaConfirmacion;

    public const ENCABEZADO = 'GESTIÓN DE CARRERAS';

    public function __construct(private readonly CarreraService $carreras) {}

    /** CU 4 pasos 1–2: P1 Gestión de Carreras. */
    public function index(): View
    {
        return view('panel.academico.carreras.index');
    }

    public function datatable(Request $request, CarreraDataTable $tabla): JsonResponse
    {
        return $tabla->respuesta($request);
    }

    /** CU 3 pasos 1–12: P2 Registrar Carrera. */
    public function create(): View
    {
        return view('panel.academico.carreras.registrar', $this->datosFormulario(new Carrera, false));
    }

    /** CU 3 pasos 14–17. */
    public function store(RegistrarCarreraRequest $request): RedirectResponse
    {
        $carrera = $this->carreras->registrar($request->validated());

        return $this->confirmar('panel.carreras.confirmacion', ['titulo' => CarreraService::CONFIRMACION_REGISTRO, 'carrera_id' => $carrera->id]);
    }

    /** CU 4 pasos 3–6: P2 Modificar con los datos actuales; con Plan de Estudio activo solo se habilita el Estado. */
    public function edit(Carrera $carrera): View
    {
        return view('panel.academico.carreras.modificar', $this->datosFormulario($carrera, $this->carreras->poseePlanActivo($carrera)));
    }

    /** CU 4 pasos 7–11. */
    public function update(ModificarCarreraRequest $request, Carrera $carrera): RedirectResponse
    {
        $carrera = $this->carreras->modificar($carrera, $request->validated());

        return $this->confirmar('panel.carreras.confirmacion', ['titulo' => CarreraService::CONFIRMACION_MODIFICACION, 'carrera_id' => $carrera->id]);
    }

    /** P3 Confirmación (Registrar y Modificar): "Aceptar" vuelve a P1. */
    public function confirmacion(Request $request): View|RedirectResponse
    {
        $datos = $this->datosConfirmacion($request);
        $carrera = $datos ? Carrera::find($datos['carrera_id'] ?? null) : null;

        if (! $carrera) {
            return redirect()->route('panel.carreras.index');
        }

        return view('panel._confirmacion', [
            'encabezado' => self::ENCABEZADO,
            'titulo' => $datos['titulo'],
            'lineas' => $this->carreras->lineasConfirmacion($carrera),
            'urlAceptar' => route('panel.carreras.index'),
        ]);
    }

    /** @return array<string, mixed> */
    private function datosFormulario(Carrera $carrera, bool $soloEstado): array
    {
        return [
            'carrera' => $carrera,
            'soloEstado' => $soloEstado,
            'modalidades' => $this->carreras->modalidades($carrera->exists ? $carrera : null)->pluck('modalidad', 'id')->all(),
            'duraciones' => $this->carreras->duraciones($carrera->exists ? $carrera : null)->pluck('duracion', 'id')->all(),
            'estados' => EstadoCarrera::opciones(),
        ];
    }
}
