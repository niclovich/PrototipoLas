<?php

namespace App\Http\Controllers\Panel\Alumnos;

use App\Exceptions\OperacionNoPermitida;
use App\Http\Controllers\Concerns\ConPantallaConfirmacion;
use App\Http\Controllers\Controller;
use App\Http\Requests\Alumnos\RegistrarInscripcionRequest;
use App\Models\Alumno;
use App\Services\Alumnos\InscripcionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * CU2 Registrar datos de Inscripción de Alumno: P2 Formulario (desde "Inscribir →" de P1 Gestión de Alumnos,
 * pestaña Preinscriptos) y P3 Confirmación. Los avisos que terminan el CU se muestran en P1.
 */
class InscripcionController extends Controller
{
    use ConPantallaConfirmacion;

    public const TITULO = 'Registrar Inscripción';

    public const ENCABEZADO = 'INSCRIPCIÓN';

    public function __construct(private readonly InscripcionService $inscripciones) {}

    /** P2: pasos 5–18. */
    public function create(Alumno $alumno): View|RedirectResponse
    {
        try {
            $this->inscripciones->verificarPreinscripto($alumno);
        } catch (OperacionNoPermitida $aviso) {
            return $this->avisoEnP1($aviso);
        }
        $alumno->load(['usuario', 'carrera']);
        $cuota = $this->inscripciones->cuotaInscripcion($alumno);

        return view('panel.alumnos.inscripcion.registrar', [
            'alumno' => $alumno,
            'presentados' => $this->inscripciones->documentosPresentados($alumno),
            'cuota' => $cuota,
            'cuotaPagada' => $this->inscripciones->cuotaPagada($alumno, $cuota),
        ]);
    }

    /** "Confirmar Inscripcion": pasos 19–26. */
    public function store(RegistrarInscripcionRequest $request, Alumno $alumno): RedirectResponse
    {
        try {
            $alumno = $this->inscripciones->registrar($alumno, $request->archivos());
        } catch (OperacionNoPermitida $aviso) {
            return $this->avisoEnP1($aviso);
        }

        return $this->confirmar('panel.inscripciones.confirmacion', ['alumno_id' => $alumno->id]);
    }

    /** P3 Confirmación: legajo generado y detalle; "Aceptar" vuelve a P1. */
    public function confirmacion(Request $request): View|RedirectResponse
    {
        $alumno = Alumno::query()->with(['usuario', 'carrera'])->find($this->datosConfirmacion($request)['alumno_id'] ?? null);

        if (! $alumno) {
            return redirect()->route('panel.alumnos.index');
        }

        return view('panel.alumnos.inscripcion.confirmacion', ['alumno' => $alumno]);
    }

    private function avisoEnP1(OperacionNoPermitida $aviso): RedirectResponse
    {
        return AlumnoController::avisar(self::TITULO, $aviso->getMessage());
    }
}
