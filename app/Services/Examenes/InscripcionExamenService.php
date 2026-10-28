<?php

namespace App\Services\Examenes;

use App\Enums\CondicionCurricular;
use App\Enums\EstadoActa;
use App\Enums\EstadoDetalleActa;
use App\Exceptions\OperacionNoPermitida;
use App\Mail\InscripcionExamenRegistrada;
use App\Models\ActaExamen;
use App\Models\Alumno;
use App\Models\Correlativa;
use App\Models\Cuota;
use App\Models\DetalleActa;
use App\Models\DetallePlanEstudio;
use App\Services\Alumnos\InscripcionComisionService;
use App\Services\Alumnos\TrayectoriaAcademicaService;
use App\Services\Finanzas\DeudaCuotasService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * CU5 "Registrar datos Inscripción de Alumnos en Exámenes" (colaboración Fig. 51): crea el DetalleActa "Activo"
 * vinculado al Alumno y al ActaExamen "Pendiente". Cuotas vencidas: DeudaCuotasService (nunca la Inscripción).
 */
class InscripcionExamenService
{
    /** Alt. 13.1. */
    public const MENSAJE_CUOTAS = 'Con las Cuotas que falta abonar';

    /** Alt. 14.1. */
    public const MENSAJE_CONDICIONES = 'El Alumno no está en condiciones académicas adecuadas para inscribirse';

    /** Acta que no está en la lista de P2 o en la que ya está inscripto (propio). */
    public const MENSAJE_ACTA_INVALIDA = 'Seleccione un acta de examen disponible.';

    public const CONFIRMACION = 'Inscripcion a Examen registrada exitosamente';

    public function __construct(
        private readonly DeudaCuotasService $deuda,
        private readonly TrayectoriaAcademicaService $trayectoria,
        private readonly InscripcionComisionService $comisiones,
    ) {}

    /** Paso 2: alumno "Inscripto" con Legajo "Activo" (mismo control y mensaje que CU3). */
    public function iniciar(Alumno $alumno): void
    {
        $this->comisiones->verificarAlumno($alumno);
    }

    /**
     * Paso 5: materias "Activo" del Plan de Estudio del alumno.
     *
     * @return EloquentCollection<int, DetallePlanEstudio>
     */
    public function materias(Alumno $alumno): EloquentCollection
    {
        return DetallePlanEstudio::query()->activos()->with('materia')
            ->where('plan_estudio_id', $alumno->plan_estudio_id)
            ->orderBy('anio_curso_det_plan_estudio')->orderBy('codigo_det_plan_estudio')
            ->get();
    }

    /**
     * Paso 8: actas "Pendiente" de la materia, con la inscripción "Activo" del alumno si ya la tiene.
     *
     * @return EloquentCollection<int, ActaExamen>
     */
    public function actasDisponibles(Alumno $alumno, DetallePlanEstudio $materia): EloquentCollection
    {
        return ActaExamen::query()
            ->where('detalle_plan_estudio_id', $materia->id)
            ->where('estado_acta', EstadoActa::Pendiente->value)
            ->with(['modalidad', 'llamado', 'tipoActa', 'inscriptos' => fn ($q) => $q->where('alumno_id', $alumno->id)])
            ->orderBy('fecha_acta')->orderBy('nro_acta')
            ->get();
    }

    /** Paso 13 (alt. 13.1/13.2: Nombre y Mes de cada cuota vencida impaga). */
    public function verificarCuotas(Alumno $alumno): void
    {
        $vencidas = $this->deuda->cuotasVencidas($alumno);

        if ($vencidas->isNotEmpty()) {
            $nombre = $alumno->usuario->nombre_completo;
            throw new OperacionNoPermitida(self::MENSAJE_CUOTAS,
                $vencidas->map(fn (Cuota $c) => "{$nombre} · {$c->mes->descripcion} {$c->anio}")->values()->all());
        }
    }

    /**
     * Paso 14 (aclaración p. 72). Regular: "Regular" en la materia, sin aprobarla, y correlativas para rendir.
     * Libre: solo que no la tenga aprobada (alt. 14.2: Materia y Condición que infringe).
     */
    public function verificarCondiciones(Alumno $alumno, ActaExamen $acta): void
    {
        $condiciones = $this->trayectoria->condiciones($alumno);
        $materia = $acta->detallePlan;
        $nombre = $materia->materia->nombre_materia;
        $infringe = [];

        if ($this->trayectoria->tiene($condiciones, $materia->id, CondicionCurricular::Aprobado)) {
            $infringe[] = "{$nombre}: ".CondicionCurricular::Aprobado->value;
        } elseif (! $acta->tipoActa->esLibre()) {
            if (! $this->trayectoria->tiene($condiciones, $materia->id, CondicionCurricular::Regular)) {
                $infringe[] = "{$nombre}: requiere ".CondicionCurricular::Regular->value;
            }
            foreach ($this->trayectoria->correlativasInfringidas($materia, $condiciones, 'p_rendir') as $c) {
                /** @var Correlativa $c */
                $infringe[] = "{$c->detallePlanCorrelativa->materia->nombre_materia}: requiere {$c->p_rendir->value}";
            }
        }

        if ($infringe !== []) {
            throw new OperacionNoPermitida(self::MENSAJE_CONDICIONES, $infringe);
        }
    }

    /** Pasos 12–17: controles, DetalleActa "Activo" (crear, cargar, vincular) y correo. */
    public function inscribir(Alumno $alumno, int $actaId): DetalleActa
    {
        return DB::transaction(function () use ($alumno, $actaId) {
            $alumno = Alumno::query()->with(['usuario', 'carrera'])->lockForUpdate()->findOrFail($alumno->id);
            $this->iniciar($alumno);

            $acta = ActaExamen::query()->with(['detallePlan.materia', 'tipoActa', 'llamado', 'modalidad'])
                ->where('estado_acta', EstadoActa::Pendiente->value)
                ->whereHas('detallePlan', fn ($q) => $q->where('plan_estudio_id', $alumno->plan_estudio_id))
                ->lockForUpdate()->find($actaId);
            if (! $acta || $acta->inscriptos()->where('alumno_id', $alumno->id)->exists()) {
                throw new OperacionNoPermitida(self::MENSAJE_ACTA_INVALIDA);
            }

            $this->verificarCuotas($alumno);
            $this->verificarCondiciones($alumno, $acta);

            $detalle = DetalleActa::query()->create([
                'acta_examen_id' => $acta->id,
                'alumno_id' => $alumno->id,
                'estado_detalle_acta' => EstadoDetalleActa::Activo,
            ]);

            Mail::to($alumno->usuario)->queue(new InscripcionExamenRegistrada($alumno, $acta));

            return $detalle;
        });
    }

    /**
     * Renglones de "Detalle de la Inscripcion" (P3).
     *
     * @return list<string>
     */
    public function lineasConfirmacion(DetalleActa $detalle): array
    {
        $detalle->loadMissing(['alumno.usuario', 'acta.detallePlan.materia', 'acta.llamado']);
        $acta = $detalle->acta;

        return [
            "Legajo: {$detalle->alumno->nro_legajo} · Alumno: {$detalle->alumno->usuario->nombre_completo}",
            "Materia: {$acta->detallePlan->materia->nombre_materia}",
            "Nro Acta: {$acta->nro_acta_texto} · Llamado: {$acta->llamado->llamado} · Fecha: {$acta->fecha_acta->format('d/m/Y')}",
            'Estado: '.$detalle->estado_detalle_acta->value,
        ];
    }
}
