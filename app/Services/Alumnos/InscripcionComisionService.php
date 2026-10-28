<?php

namespace App\Services\Alumnos;

use App\Enums\CondicionCurricular;
use App\Enums\EstadoAlumno;
use App\Enums\EstadoAlumnoComision;
use App\Enums\EstadoGeneral;
use App\Enums\EstadoLegajo;
use App\Enums\TipoInscripcion;
use App\Exceptions\OperacionNoPermitida;
use App\Mail\ComisionAsignada;
use App\Models\Alumno;
use App\Models\AlumnoComision;
use App\Models\Comision;
use App\Models\Cuota;
use App\Models\DetComision;
use App\Services\Finanzas\DeudaCuotasService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * CU3 "Registrar datos de Inscripción de Alumno en Comisiones" (colaboración Fig. 47) y CU 19 "Registrar datos de
 * Cambio de Comisión" (Fig. 152). AlumnoComision "Activo" / "Caducado", tipo "Por Comisión"; actualiza la comisión
 * actual del alumno. Las cuotas vencidas las calcula DeudaCuotasService (nunca la Inscripción).
 *
 * Una "materia evaluada" es un arreglo {det: DetComision, habilitada: bool, estado: string} (columna Estado de P3).
 */
class InscripcionComisionService
{
    /** CU3 alt. 5.1. */
    public const MENSAJE_CUOTAS_PENDIENTES = 'El alumno tiene cuotas pendientes de pago';

    /** CU3 alt. 12.1 y CU 19 alt. 9.1. */
    public const MENSAJE_CAPACIDAD = 'La comisión excede su capacidad';

    /** CU3 alt. 15.1. */
    public const MENSAJE_SIN_MATERIAS = 'El Alumno no cumple las correlatividades de ninguna materia de la Comisión';

    /** Precondición de CU3 / CU 19 (propio, E15-D2). */
    public const MENSAJE_NO_INSCRIPTO = 'El alumno no se encuentra en estado Inscripto con Legajo Activo.';

    /** Precondición de CU 19 "antes del registro de evaluaciones" (§7-26; texto propio pendiente de Nicolás). */
    public const MENSAJE_CON_EVALUACIONES = 'No se puede cambiar la comisión porque el alumno ya tiene evaluaciones registradas en la comisión actual.';

    /** CU 19 sin comisión actual del año lectivo (propio). */
    public const MENSAJE_SIN_COMISION_ACTUAL = 'El alumno no tiene una comisión del año lectivo actual para cambiar.';

    /** CU 19: la comisión de destino no dicta alguna materia a transferir (propio). */
    public const MENSAJE_MATERIAS_DESTINO = 'La comisión de destino no dicta todas las materias del alumno en la comisión actual.';

    public const MENSAJE_COMISION_INVALIDA = 'Seleccione una comisión de la lista.';

    public const HABILITADA = 'Habilitada';

    public const NO_TRANSFERIDA = 'No asignada';

    public const CONFIRMACION = 'Inscripcion registrada exitosamente';

    public const CONFIRMACION_CAMBIO = 'Cambio de Comision registrado exitosamente';

    public function __construct(
        private readonly DeudaCuotasService $deuda,
        private readonly TrayectoriaAcademicaService $trayectoria,
    ) {}

    /** Paso 2: alumno "Inscripto" con Legajo "Activo". */
    public function verificarAlumno(Alumno $alumno): void
    {
        if ($alumno->estado_alumno !== EstadoAlumno::Inscripto || $alumno->estado_legajo !== EstadoLegajo::Activo) {
            throw new OperacionNoPermitida(self::MENSAJE_NO_INSCRIPTO);
        }
    }

    /** Paso 5 (alt. 5.1 y 5.2: mensaje y listado de Mes de las cuotas vencidas impagas). */
    public function verificarCuotas(Alumno $alumno): void
    {
        $vencidas = $this->deuda->cuotasVencidas($alumno);

        if ($vencidas->isNotEmpty()) {
            throw new OperacionNoPermitida(
                self::MENSAJE_CUOTAS_PENDIENTES,
                $vencidas->map(fn (Cuota $cuota) => "Cuota {$cuota->mes->descripcion} {$cuota->anio}")->values()->all(),
            );
        }
    }

    /** Pasos 2–5 de CU3 (entrada a P2 desde el ícono Comision). */
    public function iniciar(Alumno $alumno): void
    {
        $this->verificarAlumno($alumno);
        $this->verificarCuotas($alumno);
    }

    /**
     * Paso 6: comisiones en las que estuvo inscripto (Activo o Caducado), de la más reciente a la más antigua.
     *
     * @return Collection<int, Comision>
     */
    public function historial(Alumno $alumno): Collection
    {
        return Comision::query()->with('modalidad')
            ->whereHas('detalles.inscripciones', fn (Builder $q) => $q->where('alumno_id', $alumno->id))
            ->orderByDesc('anio_lectivo')->orderByDesc('anio_curso')->orderBy('codigo_comision')
            ->get();
    }

    /** "Cambiar" de P2: la comisión actual, si es del año lectivo actual. */
    public function esCambiable(Alumno $alumno, Comision $comision): bool
    {
        return (int) $alumno->comision_actual_id === (int) $comision->id && $comision->anio_lectivo === (int) now()->year;
    }

    /**
     * Paso 9: comisiones "Activo" de la carrera del alumno (sin las que ya cursa con inscripciones "Activo").
     *
     * @return EloquentCollection<int, Comision>
     */
    public function comisionesDisponibles(Alumno $alumno): EloquentCollection
    {
        return Comision::query()->activas()->with('modalidad')
            ->where('carrera_id', $alumno->carrera_id)
            ->whereDoesntHave('detalles.inscripciones', fn (Builder $q) => $q->where('alumno_id', $alumno->id)->activas())
            ->orderByDesc('anio_lectivo')->orderBy('anio_curso')->orderBy('codigo_comision')
            ->get();
    }

    /**
     * Pasos 12–15: capacidad (alt. 12.1) y materias asignables según correlatividades y estado curricular (alt. 14.1).
     *
     * @return list<array{det: DetComision, habilitada: bool, estado: string}>
     */
    public function evaluar(Alumno $alumno, Comision $comision): array
    {
        $this->verificarCapacidad($comision);
        $condiciones = $this->trayectoria->condiciones($alumno);

        return $this->materiasDe($comision)
            ->map(function (DetComision $det) use ($condiciones) {
                $infringe = $this->correlatividadInfringida($det, $condiciones);

                return ['det' => $det, 'habilitada' => $infringe === null, 'estado' => $infringe ?? self::HABILITADA];
            })
            ->values()
            ->all();
    }

    /**
     * @param  list<array{det: DetComision, habilitada: bool, estado: string}>  $materias
     */
    public function hayHabilitadas(array $materias): bool
    {
        return collect($materias)->contains('habilitada', true);
    }

    /**
     * Pasos 17–21: una inscripción "Activo" / "Por Comisión" por materia habilitada; la comisión pasa a ser la actual.
     *
     * @return array{comision: Comision, cantidad: int}
     */
    public function inscribir(Alumno $alumno, int $comisionId): array
    {
        return DB::transaction(function () use ($alumno, $comisionId) {
            $alumno = Alumno::query()->with(['usuario', 'carrera'])->lockForUpdate()->findOrFail($alumno->id);
            $this->iniciar($alumno);

            $comision = $this->comisionesDisponibles($alumno)->find($comisionId)
                ?? throw new OperacionNoPermitida(self::MENSAJE_COMISION_INVALIDA);
            Comision::query()->whereKey($comision->id)->lockForUpdate()->first();

            $habilitadas = collect($this->evaluar($alumno, $comision))->where('habilitada', true);
            if ($habilitadas->isEmpty()) {
                throw new OperacionNoPermitida(self::MENSAJE_SIN_MATERIAS);
            }

            foreach ($habilitadas as $materia) {
                $this->crearInscripcion($alumno, $materia['det']);
            }
            $alumno->update(['comision_actual_id' => $comision->id]);

            Mail::to($alumno->usuario)->queue(new ComisionAsignada($alumno, $comision));

            return ['comision' => $comision, 'cantidad' => $habilitadas->count()];
        });
    }

    /**
     * CU 19 pasos 4–5: comisión actual del año lectivo, sin evaluaciones registradas (§7-26).
     */
    public function comisionParaCambio(Alumno $alumno): Comision
    {
        $this->verificarAlumno($alumno);
        $actual = $alumno->comisionActual()->with('modalidad')->first();

        if (! $actual || ! $this->esCambiable($alumno, $actual) || $this->inscripcionesEn($alumno, $actual)->isEmpty()) {
            throw new OperacionNoPermitida(self::MENSAJE_SIN_COMISION_ACTUAL);
        }

        $conEvaluaciones = $this->inscripcionesEn($alumno, $actual)->contains(fn (AlumnoComision $ac) => $ac->condicion_final !== null
            || collect(['nota_p1', 'nota_p2', 'nota_r1', 'nota_r2'])->contains(fn (string $nota) => $ac->{$nota} !== null));
        if ($conEvaluaciones) {
            throw new OperacionNoPermitida(self::MENSAJE_CON_EVALUACIONES);
        }

        return $actual;
    }

    /**
     * CU 19 paso 6: comisiones "Activo" de la misma Carrera, Año de Curso y Año Lectivo, sin la actual.
     *
     * @return EloquentCollection<int, Comision>
     */
    public function comisionesDestino(Comision $origen): EloquentCollection
    {
        return Comision::query()->activas()->with('modalidad')
            ->where('carrera_id', $origen->carrera_id)->where('anio_curso', $origen->anio_curso)
            ->where('anio_lectivo', $origen->anio_lectivo)->whereKeyNot($origen->id)
            ->orderBy('codigo_comision')
            ->get();
    }

    /**
     * CU 19 pasos 9–11: capacidad (alt. 9.1) y materias de la nueva comisión; "Habilitada" = se transfiere.
     *
     * @return list<array{det: DetComision, habilitada: bool, estado: string}>
     */
    public function evaluarCambio(Alumno $alumno, Comision $origen, Comision $destino): array
    {
        $this->verificarCapacidad($destino);
        $aTransferir = $this->inscripcionesEn($alumno, $origen)->map(fn (AlumnoComision $ac) => (int) $ac->detComision->detalle_plan_estudio_id);
        $materias = $this->materiasDe($destino);

        if ($aTransferir->diff($materias->map(fn (DetComision $d) => (int) $d->detalle_plan_estudio_id))->isNotEmpty()) {
            throw new OperacionNoPermitida(self::MENSAJE_MATERIAS_DESTINO);
        }

        return $materias->map(function (DetComision $det) use ($aTransferir) {
            $transfiere = $aTransferir->contains((int) $det->detalle_plan_estudio_id);

            return ['det' => $det, 'habilitada' => $transfiere, 'estado' => $transfiere ? self::HABILITADA : self::NO_TRANSFERIDA];
        })->values()->all();
    }

    /**
     * CU 19 pasos 12–17: caduca cada inscripción de la comisión de origen y crea la de la misma materia en la de destino.
     *
     * @return array{origen: Comision, comision: Comision, cantidad: int}
     */
    public function cambiar(Alumno $alumno, int $destinoId): array
    {
        return DB::transaction(function () use ($alumno, $destinoId) {
            $alumno = Alumno::query()->with(['usuario', 'carrera'])->lockForUpdate()->findOrFail($alumno->id);
            $origen = $this->comisionParaCambio($alumno);
            $destino = $this->comisionesDestino($origen)->find($destinoId)
                ?? throw new OperacionNoPermitida(self::MENSAJE_COMISION_INVALIDA);
            Comision::query()->whereKey($destino->id)->lockForUpdate()->first();

            $this->evaluarCambio($alumno, $origen, $destino);
            $detallesDestino = $this->materiasDe($destino)->keyBy('detalle_plan_estudio_id');
            $inscripciones = $this->inscripcionesEn($alumno, $origen);

            foreach ($inscripciones as $inscripcion) {
                $inscripcion->update(['estado_alumno_comision' => EstadoAlumnoComision::Caducado]);
                $this->crearInscripcion($alumno, $detallesDestino[$inscripcion->detComision->detalle_plan_estudio_id]);
            }
            $alumno->update(['comision_actual_id' => $destino->id]);

            Mail::to($alumno->usuario)->queue(new ComisionAsignada($alumno, $destino, true));

            return ['origen' => $origen, 'comision' => $destino, 'cantidad' => $inscripciones->count()];
        });
    }

    private function verificarCapacidad(Comision $comision): void
    {
        $inscriptos = AlumnoComision::query()->activas()
            ->whereHas('detComision', fn (Builder $q) => $q->where('comision_id', $comision->id))
            ->distinct()->count('alumno_id');

        if ($inscriptos >= $comision->capacidad_comision) {
            throw new OperacionNoPermitida(self::MENSAJE_CAPACIDAD);
        }
    }

    /** @return EloquentCollection<int, DetComision> */
    private function materiasDe(Comision $comision): EloquentCollection
    {
        return $comision->detalles()
            ->where('estado_det_comision', EstadoGeneral::Activo->value)
            ->with(['detallePlan.materia', 'detallePlan.correlativas.detallePlanCorrelativa.materia'])
            ->get()
            ->sortBy(fn (DetComision $d) => $d->detallePlan->materia->nombre_materia)
            ->values();
    }

    /** @return EloquentCollection<int, AlumnoComision> */
    private function inscripcionesEn(Alumno $alumno, Comision $comision): EloquentCollection
    {
        return $alumno->inscripcionesComision()->activas()->with('detComision')
            ->whereHas('detComision', fn (Builder $q) => $q->where('comision_id', $comision->id))
            ->get();
    }

    /**
     * Paso 14: primera correlatividad "para cursar" que el alumno no cumple (TrayectoriaAcademicaService).
     *
     * @param  Collection<int, Collection<int, CondicionCurricular>>  $condiciones
     */
    private function correlatividadInfringida(DetComision $det, Collection $condiciones): ?string
    {
        $infringida = $this->trayectoria->correlativasInfringidas($det->detallePlan, $condiciones, 'p_cursar')->first();

        return $infringida
            ? "No asignable: requiere {$infringida->detallePlanCorrelativa->materia->nombre_materia} {$infringida->p_cursar->value}"
            : null;
    }

    private function crearInscripcion(Alumno $alumno, DetComision $det): void
    {
        AlumnoComision::query()->create([
            'alumno_id' => $alumno->id,
            'det_comision_id' => $det->id,
            'fecha_inscripcion' => today(),
            'estado_alumno_comision' => EstadoAlumnoComision::Activo,
            'tipo_inscripcion' => TipoInscripcion::PorComision,
        ]);
    }
}
