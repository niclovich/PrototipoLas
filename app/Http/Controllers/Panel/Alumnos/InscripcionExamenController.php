<?php

namespace App\Http\Controllers\Panel\Alumnos;

use App\Exceptions\OperacionNoPermitida;
use App\Http\Controllers\Concerns\ConPantallaConfirmacion;
use App\Http\Controllers\Controller;
use App\Models\Alumno;
use App\Models\DetalleActa;
use App\Services\Documentos\PdfService;
use App\Services\Examenes\InscripcionExamenService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

/**
 * CU5 Inscripción a Exámenes (Preceptor y Alumno): P2 Actas de Examen Disponibles (desde el ícono "Examen" de
 * P1 Gestión de Alumnos), P3 Confirmación y el Comprobante de Inscripción en PDF. AlumnoPolicy: el Alumno solo lo suyo.
 */
class InscripcionExamenController extends Controller
{
    use ConPantallaConfirmacion;

    public const ENCABEZADO = 'INSCRIPCION DE ALUMNO EN EXAMENES';

    public const TITULO = 'Inscripción a Exámenes';

    /** Aviso de P2 (acta no disponible). */
    public const AVISO = 'aviso_examenes';

    public function __construct(private readonly InscripcionExamenService $examenes) {}

    /** P2: pasos 2–9 (combo "Seleccionar Materia" con ?materia). */
    public function create(Request $request, Alumno $alumno): View|RedirectResponse
    {
        Gate::authorize('gestionarExamenes', $alumno);
        try {
            $this->examenes->iniciar($alumno);
        } catch (OperacionNoPermitida $aviso) {
            return AlumnoController::avisar(self::TITULO, $aviso->getMessage());
        }

        $materias = $this->examenes->materias($alumno);
        $seleccionada = $materias->firstWhere('id', $request->integer('materia'));

        return view('panel.alumnos.examenes.inscribir', [
            'alumno' => $alumno->load(['usuario', 'carrera']),
            'materias' => $materias,
            'seleccionada' => $seleccionada,
            'actas' => $seleccionada ? $this->examenes->actasDisponibles($alumno, $seleccionada) : collect(),
        ]);
    }

    /** "Inscribir": pasos 12–17 (alt. 13 y 14 vuelven a P1). */
    public function store(Request $request, Alumno $alumno): RedirectResponse
    {
        Gate::authorize('gestionarExamenes', $alumno);
        try {
            $detalle = $this->examenes->inscribir($alumno, $request->integer('acta_id'));
        } catch (OperacionNoPermitida $aviso) {
            if ($aviso->getMessage() === InscripcionExamenService::MENSAJE_ACTA_INVALIDA) {
                return redirect()->route('panel.inscripciones-examen.create', [$alumno, 'materia' => $request->integer('materia')])
                    ->with(self::AVISO, $aviso->getMessage());
            }

            return AlumnoController::avisar(self::TITULO, $aviso->getMessage(), $aviso->lista);
        }

        return $this->confirmar('panel.inscripciones-examen.confirmacion', ['detalle_acta_id' => $detalle->id]);
    }

    /** P3 Confirmación: "Descargar Comprobante" y "Aceptar" (→ P1). */
    public function confirmacion(Request $request): View|RedirectResponse
    {
        $detalle = DetalleActa::query()->with('alumno.usuario')->find($this->datosConfirmacion($request)['detalle_acta_id'] ?? null);

        if (! $detalle) {
            return redirect()->route('panel.alumnos.index');
        }

        return view('panel.alumnos.examenes.confirmacion', [
            'detalle' => $detalle,
            'lineas' => $this->examenes->lineasConfirmacion($detalle),
        ]);
    }

    /** Comprobante de Inscripción a Examen (Fig. 82) con QR a la validación pública (paso 16). */
    public function comprobante(DetalleActa $detalleActa, PdfService $pdf): Response
    {
        Gate::authorize('gestionarExamenes', $detalleActa->alumno);
        $detalleActa->load(['alumno.usuario', 'acta.detallePlan.materia', 'acta.modalidad', 'acta.llamado']);

        return $pdf->generar('pdf.comprobante-inscripcion-examen', ['detalle' => $detalleActa],
            route('validar.inscripcion-examen', $detalleActa->uuid))
            ->download('inscripcion-examen-'.$detalleActa->acta->nro_acta_texto.'-'.$detalleActa->alumno->nro_legajo.'.pdf');
    }
}
