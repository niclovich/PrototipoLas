<?php

namespace App\Services\Examenes;

use App\Enums\Asentada;
use App\Enums\CondicionCurricular;
use App\Enums\CondicionExamen;
use App\Enums\EstadoActa;
use App\Enums\EstadoAlumno;
use App\Enums\EstadoTramite;
use App\Exceptions\OperacionNoPermitida;
use App\Mail\ResultadoExamen;
use App\Models\ActaExamen;
use App\Models\DetalleActa;
use App\Models\EstadoCurricular;
use App\Models\TituloTramite;
use App\Services\Alumnos\TrayectoriaAcademicaService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * CU6 "Registrar datos de Asentamiento de Acta" (colaboración Fig. 53). La nota de cada inscripto se guarda en su
 * DetalleActa desde P3 (estados Fig. 83: "Guardar"); "Asentar Acta" crea los EstadoCurricular con nroActa, el
 * TituloTramite "Sin Gestión" si el alumno completó el plan, y deja el acta "Finalizado", asentada S y con folio.
 */
class AsentamientoActaService
{
    /** Alt. 10.1. */
    public const MENSAJE_NOTA = 'La nota ingresada no está dentro del rango permitido';

    /** Alt. 16.1. */
    public const MENSAJE_FOLIO = 'El número de folio es inválido';

    /** Acta que ya no está "Pendiente" (propio). */
    public const MENSAJE_ACTA_ASENTADA = 'El acta de examen ya fue asentada.';

    /** "Asentar Acta" con alumnos sin nota (propio; el botón está deshabilitado). */
    public const MENSAJE_INCOMPLETA = 'Todos los alumnos inscriptos deben tener su nota registrada.';

    public const NOTA_APROBACION = 4;

    public const SIN_REGISTRAR = 'Sin registrar';

    public const CONFIRMACION = 'Acta de Examen asentada exitosamente';

    public const CONFIRMACION_DETALLE = 'Se registro el numero de folio y el acta quedo en estado Asentada.';

    public function __construct(private readonly TrayectoriaAcademicaService $trayectoria) {}

    /** Paso 2: solo actas "Pendiente". */
    public function asegurarPendiente(ActaExamen $acta): void
    {
        if ($acta->estado_acta !== EstadoActa::Pendiente) {
            throw new OperacionNoPermitida(self::MENSAJE_ACTA_ASENTADA);
        }
    }

    /**
     * Paso 5: inscriptos ("Activo") por legajo.
     *
     * @return EloquentCollection<int, DetalleActa>
     */
    public function inscriptos(ActaExamen $acta): EloquentCollection
    {
        return $acta->inscriptos()->with('alumno.usuario')->get()->sortBy(fn (DetalleActa $d) => $d->alumno->nro_legajo)->values();
    }

    /** "Se habilita una vez que todos los alumnos tienen su nota…" (P2). */
    public function completa(ActaExamen $acta): bool
    {
        return ! $acta->inscriptos()->whereNull('nota_acta')->exists();
    }

    /** Paso 5.2 de la colaboración (aclaración p. 74): Aprobado si nota ≥ 4. */
    public function calcularCondicionFinal(float $nota): CondicionExamen
    {
        return $nota >= self::NOTA_APROBACION ? CondicionExamen::Aprobado : CondicionExamen::Desaprobado;
    }

    /** Pasos 7–13 (P3 "Guardar"): nota, observación y condición final calculada. */
    public function registrarNota(DetalleActa $detalle, float $nota, ?string $observacion): void
    {
        DB::transaction(function () use ($detalle, $nota, $observacion) {
            $this->asegurarPendiente(ActaExamen::query()->lockForUpdate()->findOrFail($detalle->acta_examen_id));
            $detalle->update([
                'nota_acta' => $nota,
                'observacion' => $observacion,
                'condicion_final' => $this->calcularCondicionFinal($nota),
            ]);
        });
    }

    /** Pasos 19–24: EstadoCurricular por alumno, Trámite de Título si completó el plan y acta "Finalizado". */
    public function asentar(ActaExamen $acta, int $nroFolio): ActaExamen
    {
        return DB::transaction(function () use ($acta, $nroFolio) {
            $acta = ActaExamen::query()->lockForUpdate()->findOrFail($acta->id);
            $this->asegurarPendiente($acta);
            if (! $this->completa($acta)) {
                throw new OperacionNoPermitida(self::MENSAJE_INCOMPLETA);
            }

            foreach ($this->inscriptos($acta) as $detalle) {
                $alumno = $detalle->alumno;
                EstadoCurricular::query()->create([
                    'alumno_id' => $alumno->id,
                    'detalle_plan_estudio_id' => $acta->detalle_plan_estudio_id,
                    'condicion' => CondicionCurricular::from($detalle->condicion_final->value),
                    'fecha_condicion' => $acta->fecha_acta,
                    'nota_condicion' => $detalle->nota_acta,
                    'nro_acta' => $acta->nro_acta,
                ]);

                if ($detalle->condicion_final === CondicionExamen::Aprobado && $this->trayectoria->planCompleto($alumno)
                    && ! $alumno->tituloTramite()->exists()) {
                    TituloTramite::query()->create([
                        'alumno_id' => $alumno->id,
                        'fecha_creacion' => today(),
                        'estado_tramite' => EstadoTramite::SinGestion,
                    ]);
                    $alumno->update(['estado_alumno' => EstadoAlumno::TituloEnTramite]);
                }

                Mail::to($alumno->usuario)->queue(new ResultadoExamen($alumno, $detalle));
            }

            $acta->update(['nro_folio' => $nroFolio, 'estado_acta' => EstadoActa::Finalizado, 'asentada' => Asentada::Si]);

            return $acta;
        });
    }

    /**
     * "Detalle del Asentamiento" de P4.
     *
     * @return list<string>
     */
    public function lineasConfirmacion(ActaExamen $acta): array
    {
        $acta->loadMissing('detallePlan.materia');
        $inscriptos = $this->inscriptos($acta);
        $aprobados = $inscriptos->where('condicion_final', CondicionExamen::Aprobado)->count();
        $desaprobados = $inscriptos->where('condicion_final', CondicionExamen::Desaprobado)->count();

        return [
            "Nro Acta: {$acta->nro_acta_texto} · Nro Folio: {$acta->nro_folio}",
            "Materia: {$acta->detallePlan->materia->nombre_materia} · Fecha: {$acta->fecha_acta->format('d/m/Y')}",
            "Alumnos aprobados: {$aprobados} · Desaprobados: {$desaprobados} · Ausentes: ".($inscriptos->count() - $aprobados - $desaprobados),
            'Estado del Acta: '.($acta->asentada === Asentada::Si ? 'Asentada' : $acta->estado_acta->value),
        ];
    }
}
