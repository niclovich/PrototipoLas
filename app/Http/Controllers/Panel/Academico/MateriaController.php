<?php

namespace App\Http\Controllers\Panel\Academico;

use App\DataTables\MateriaDataTable;
use App\Enums\EstadoGeneral;
use App\Exceptions\OperacionNoPermitida;
use App\Http\Controllers\Concerns\ConPantallaConfirmacion;
use App\Http\Controllers\Controller;
use App\Http\Requests\Academico\ModificarMateriaRequest;
use App\Http\Requests\Academico\RegistrarMateriaRequest;
use App\Models\Materia;
use App\Services\Academico\MateriaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Gestión de Materias (Coordinador): P1 (entrada de CU 1 y CU 2), P2 Registrar, P2 Modificar y P3 Confirmación.
 */
class MateriaController extends Controller
{
    use ConPantallaConfirmacion;

    public const ENCABEZADO = 'GESTIÓN DE MATERIAS';

    /** Clave de sesión del aviso que P1 muestra en un modal (CU 2 alt. 4). */
    public const AVISO = 'aviso_materia';

    public function __construct(private readonly MateriaService $materias) {}

    /** CU 2 pasos 1–2: P1 Gestión de Materias. */
    public function index(): View
    {
        return view('panel.academico.materias.index');
    }

    public function datatable(Request $request, MateriaDataTable $tabla): JsonResponse
    {
        return $tabla->respuesta($request);
    }

    /** CU 1 pasos 1–3: P2 Registrar Materia. */
    public function create(): View
    {
        return view('panel.academico.materias.registrar', ['materia' => new Materia(['estado_materia' => EstadoGeneral::Activo])]);
    }

    /** CU 1 pasos 4–8. */
    public function store(RegistrarMateriaRequest $request): RedirectResponse
    {
        $materia = $this->materias->registrar($request->validated('nombre_materia'));

        return $this->confirmar('panel.materias.confirmacion', ['titulo' => MateriaService::CONFIRMACION_REGISTRO, 'materia_id' => $materia->id]);
    }

    /** CU 2 pasos 3, 5: P2 Modificar con los datos actuales (la vinculación se controla al guardar, D2). */
    public function edit(Materia $materia): View
    {
        return view('panel.academico.materias.modificar', ['materia' => $materia]);
    }

    /** CU 2 pasos 4 (D2), 6–11. Si está vinculada a un Plan de Estudio activo: aviso en P1 y fin del CU. */
    public function update(ModificarMateriaRequest $request, Materia $materia): RedirectResponse
    {
        try {
            $materia = $this->materias->modificar(
                $materia,
                $request->validated('nombre_materia'),
                EstadoGeneral::from($request->validated('estado_materia')),
            );
        } catch (OperacionNoPermitida $aviso) {
            return redirect()->route('panel.materias.index')->with(self::AVISO, $aviso->getMessage());
        }

        return $this->confirmar('panel.materias.confirmacion', ['titulo' => MateriaService::CONFIRMACION_MODIFICACION, 'materia_id' => $materia->id]);
    }

    /** P3 Confirmación (Registrar y Modificar): "Aceptar" vuelve a P1. */
    public function confirmacion(Request $request): View|RedirectResponse
    {
        $datos = $this->datosConfirmacion($request);
        $materia = $datos ? Materia::find($datos['materia_id'] ?? null) : null;

        if (! $materia) {
            return redirect()->route('panel.materias.index');
        }

        return view('panel._confirmacion', [
            'encabezado' => self::ENCABEZADO,
            'titulo' => $datos['titulo'],
            'lineas' => $this->materias->lineasConfirmacion($materia),
            'urlAceptar' => route('panel.materias.index'),
        ]);
    }
}
