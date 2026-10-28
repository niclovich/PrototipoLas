<?php

namespace App\Http\Controllers\Panel\Alumnos;

use App\Enums\CondicionCursada;
use App\Exceptions\OperacionNoPermitida;
use App\Http\Controllers\Concerns\ConPantallaConfirmacion;
use App\Http\Controllers\Controller;
use App\Http\Requests\Alumnos\RegistrarRegularidadRequest;
use App\Models\AlumnoComision;
use App\Models\Comision;
use App\Models\DetComision;
use App\Services\Alumnos\RegularidadService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * CU4 Registro de Regularidad Académica por Materia (Preceptor / Docente): P1 Gestión de Comisiones, P2 Materias de
 * la Comisión, P3 Alumnos Inscriptos, P4 Formulario y P5 Confirmación.
 */
class RegularidadController extends Controller
{
    use ConPantallaConfirmacion;

    public const ENCABEZADO = 'REGISTRO DE REGULARIDAD ACADEMICA';

    public const AVISO = 'aviso_regularidad';

    public function __construct(private readonly RegularidadService $regularidad) {}

    /** P1: pasos 2–7 (combo de carrera; sin elegir, la primera). */
    public function index(Request $request): View
    {
        $carreras = $this->regularidad->carreras();
        $carrera = $carreras->firstWhere('id', (int) $request->query('carrera_id')) ?? $carreras->first();

        return view('panel.alumnos.regularidad.index', [
            'carreras' => $carreras->pluck('nombre_carrera', 'id')->all(),
            'carrera' => $carrera,
            'comisiones' => $this->regularidad->comisiones($carrera),
        ]);
    }

    /** P2: pasos 8–10. */
    public function materias(Comision $comision): View
    {
        return view('panel.alumnos.regularidad.materias', [
            'comision' => $comision->load('carrera'),
            'grupos' => $this->regularidad->materiasPorCuatrimestre($comision),
        ]);
    }

    /** P3: pasos 11–13. */
    public function alumnos(DetComision $detComision): View
    {
        return view('panel.alumnos.regularidad.alumnos', [
            'det' => $detComision->load(['comision', 'detallePlan.materia']),
            'inscriptos' => $this->regularidad->inscriptos($detComision),
        ]);
    }

    /** P4: pasos 14–21. */
    public function edit(AlumnoComision $inscripcion): View
    {
        return view('panel.alumnos.regularidad.registrar', [
            'inscripcion' => $inscripcion->load(['alumno.usuario', 'detComision.comision', 'detComision.detallePlan.materia']),
            'condiciones' => CondicionCursada::opciones(),
        ]);
    }

    /** "Registrar Regularidad": pasos 22–25. */
    public function update(RegistrarRegularidadRequest $request, AlumnoComision $inscripcion): RedirectResponse
    {
        try {
            $inscripcion = $this->regularidad->registrar($inscripcion, $request->datos());
        } catch (OperacionNoPermitida $aviso) {
            return redirect()->route('panel.regularidad.edit', $inscripcion)->with(self::AVISO, $aviso->getMessage());
        }

        return $this->confirmar('panel.regularidad.confirmacion', ['inscripcion_id' => $inscripcion->id]);
    }

    /** P5 Confirmación; "Aceptar" vuelve a P3 (alumnos inscriptos de la materia). */
    public function confirmacion(Request $request): View|RedirectResponse
    {
        $inscripcion = AlumnoComision::query()
            ->with(['alumno.usuario', 'detComision.comision', 'detComision.detallePlan.materia'])
            ->find($this->datosConfirmacion($request)['inscripcion_id'] ?? null);

        if (! $inscripcion) {
            return redirect()->route('panel.regularidad.index');
        }

        return view('panel.alumnos.regularidad.confirmacion', ['inscripcion' => $inscripcion]);
    }
}
