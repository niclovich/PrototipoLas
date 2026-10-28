<?php

namespace App\Http\Controllers\Panel\Alumnos;

use App\Exceptions\OperacionNoPermitida;
use App\Http\Controllers\Concerns\ConPantallaConfirmacion;
use App\Http\Controllers\Controller;
use App\Models\Alumno;
use App\Models\Comision;
use App\Services\Alumnos\InscripcionComisionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * CU3 Inscripción en Comisiones (P2 Historial, P3 Formulario, P4 Confirmación) y CU 19 Cambio de Comisión
 * (P3 Formulario Cambiar Comisión y su Confirmación), desde el ícono "Comision" de P1 Gestión de Alumnos.
 */
class InscripcionComisionController extends Controller
{
    use ConPantallaConfirmacion;

    public const ENCABEZADO = 'ALUMNO COMISIONES';

    public const ENCABEZADO_CONFIRMACION = 'INSCRIPCION DE ALUMNO EN COMISIONES';

    public const TITULO = 'Inscripción en Comisiones';

    /** Aviso de P2 (p. ej. §7-26 al pulsar "Cambiar"). */
    public const AVISO = 'aviso_comisiones';

    public function __construct(private readonly InscripcionComisionService $comisiones) {}

    /** P2 Historial de comisiones: pasos 2–7 (alt. 5.1/5.2 en P1). */
    public function index(Alumno $alumno): View|RedirectResponse
    {
        try {
            $this->comisiones->iniciar($alumno);
        } catch (OperacionNoPermitida $aviso) {
            return AlumnoController::avisar(self::TITULO, $aviso->getMessage(), $aviso->lista);
        }

        $historial = $this->comisiones->historial($alumno);

        return view('panel.alumnos.comisiones.historial', [
            'alumno' => $alumno->load(['usuario', 'carrera']),
            'historial' => $historial,
            'cambiable' => $historial->first(fn (Comision $c) => $this->comisiones->esCambiable($alumno, $c))?->id,
        ]);
    }

    /** P3 Formulario de inscripción: pasos 8–16 (combo; al elegir, alt. 12.1 / 14.1 / 15.1). */
    public function create(Request $request, Alumno $alumno): View|RedirectResponse
    {
        try {
            $this->comisiones->iniciar($alumno);
        } catch (OperacionNoPermitida $aviso) {
            return AlumnoController::avisar(self::TITULO, $aviso->getMessage(), $aviso->lista);
        }

        $disponibles = $this->comisiones->comisionesDisponibles($alumno);
        [$seleccionada, $materias, $aviso] = $this->evaluarSeleccion($request, $disponibles,
            fn (Comision $c) => $this->comisiones->evaluar($alumno, $c));
        if ($seleccionada && $materias !== null && ! $this->comisiones->hayHabilitadas($materias)) {
            $aviso = InscripcionComisionService::MENSAJE_SIN_MATERIAS;
        }

        return view('panel.alumnos.comisiones.inscribir', [
            'alumno' => $alumno->load(['usuario', 'carrera']),
            'opciones' => $disponibles->mapWithKeys(fn (Comision $c) => [$c->id => $c->etiqueta()])->all(),
            'seleccionada' => $seleccionada,
            'materias' => $materias,
            'aviso' => $aviso ?? session(self::AVISO),
        ]);
    }

    /** "Registrar Inscripcion": pasos 17–21. */
    public function store(Request $request, Alumno $alumno): RedirectResponse
    {
        try {
            $resultado = $this->comisiones->inscribir($alumno, (int) $request->input('comision_id'));
        } catch (OperacionNoPermitida $aviso) {
            return redirect()->route('panel.alumno-comisiones.create', [$alumno, 'comision_id' => $request->input('comision_id')])
                ->with(self::AVISO, $aviso->getMessage());
        }

        return $this->confirmar('panel.alumno-comisiones.confirmacion', [
            'alumno_id' => $alumno->id, 'comision_id' => $resultado['comision']->id, 'cantidad' => $resultado['cantidad'],
        ]);
    }

    /** CU 19 P3 Formulario Cambiar Comisión: pasos 4–11 (alt. 9.1; §7-26 vuelve a P2). */
    public function cambio(Request $request, Alumno $alumno): View|RedirectResponse
    {
        try {
            $origen = $this->comisiones->comisionParaCambio($alumno);
        } catch (OperacionNoPermitida $aviso) {
            return redirect()->route('panel.alumno-comisiones.index', $alumno)->with(self::AVISO, $aviso->getMessage());
        }

        $destinos = $this->comisiones->comisionesDestino($origen);
        [$seleccionada, $materias, $aviso] = $this->evaluarSeleccion($request, $destinos,
            fn (Comision $c) => $this->comisiones->evaluarCambio($alumno, $origen, $c));

        return view('panel.alumnos.comisiones.cambiar', [
            'alumno' => $alumno->load(['usuario', 'carrera']),
            'origen' => $origen,
            'opciones' => $destinos->mapWithKeys(fn (Comision $c) => [$c->id => $c->etiqueta()])->all(),
            'seleccionada' => $seleccionada,
            'materias' => $materias,
            'aviso' => $aviso ?? session(self::AVISO),
        ]);
    }

    /** "Cambiar Comision": pasos 12–17. */
    public function cambiar(Request $request, Alumno $alumno): RedirectResponse
    {
        try {
            $resultado = $this->comisiones->cambiar($alumno, (int) $request->input('comision_id'));
        } catch (OperacionNoPermitida $aviso) {
            return redirect()->route('panel.alumno-comisiones.cambio', [$alumno, 'comision_id' => $request->input('comision_id')])
                ->with(self::AVISO, $aviso->getMessage());
        }

        return $this->confirmar('panel.alumno-comisiones.cambio.confirmacion', [
            'alumno_id' => $alumno->id, 'comision_id' => $resultado['comision']->id, 'cantidad' => $resultado['cantidad'],
            'origen_id' => $resultado['origen']->id,
        ]);
    }

    /** P4 Confirmación de CU3. */
    public function confirmacion(Request $request): View|RedirectResponse
    {
        return $this->pantallaConfirmacion($request, false);
    }

    /** P4 Confirmación de CU 19. */
    public function confirmacionCambio(Request $request): View|RedirectResponse
    {
        return $this->pantallaConfirmacion($request, true);
    }

    private function pantallaConfirmacion(Request $request, bool $cambio): View|RedirectResponse
    {
        $datos = $this->datosConfirmacion($request) ?? [];
        $alumno = Alumno::query()->with(['usuario', 'carrera'])->find($datos['alumno_id'] ?? null);
        $comision = Comision::query()->find($datos['comision_id'] ?? null);

        if (! $alumno || ! $comision) {
            return redirect()->route('panel.alumnos.index');
        }

        return view('panel.alumnos.comisiones.confirmacion', [
            'alumno' => $alumno,
            'comision' => $comision,
            'cantidad' => (int) $datos['cantidad'],
            'origen' => $cambio ? Comision::query()->find($datos['origen_id'] ?? null) : null,
            'cambio' => $cambio,
        ]);
    }

    /**
     * Comisión elegida en el combo (?comision_id) y su evaluación; si una regla la rechaza, su mensaje va al modal.
     *
     * @param  iterable<int, Comision>  $opciones
     * @return array{0: ?Comision, 1: ?array, 2: ?string}
     */
    private function evaluarSeleccion(Request $request, $opciones, callable $evaluar): array
    {
        $seleccionada = collect($opciones)->firstWhere('id', (int) $request->query('comision_id'));
        if (! $seleccionada) {
            return [null, null, null];
        }

        try {
            return [$seleccionada, $evaluar($seleccionada), null];
        } catch (OperacionNoPermitida $aviso) {
            return [$seleccionada, null, $aviso->getMessage()];
        }
    }
}
