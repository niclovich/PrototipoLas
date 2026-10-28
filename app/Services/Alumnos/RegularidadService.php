<?php

namespace App\Services\Alumnos;

use App\Enums\CondicionCurricular;
use App\Enums\CondicionCursada;
use App\Enums\EstadoAlumnoComision;
use App\Enums\EstadoGeneral;
use App\Exceptions\OperacionNoPermitida;
use App\Mail\CondicionRegistrada;
use App\Models\AlumnoComision;
use App\Models\Carrera;
use App\Models\Comision;
use App\Models\DetComision;
use App\Models\EstadoCurricular;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * CU4 "Registro de Regularidad Académica por Materia" (colaboración Fig. 49). El actor (Preceptor o Docente, que ve
 * todas las comisiones, §7-14) elige la Condición Final (Regular/Libre); "En Curso" solo se muestra. Cada registro
 * actualiza el AlumnoComision y crea SIEMPRE un nuevo EstadoCurricular (se conserva el historial).
 */
class RegularidadService
{
    /** Alt. 17.1. */
    public const MENSAJE_FORMATO_NOTA = 'La nota de evaluación no cumple con el formato.';

    /** Inscripción no "Activo" (propio). */
    public const MENSAJE_INSCRIPCION_CADUCADA = 'La inscripción del alumno en la materia no está Activo.';

    public const CONFIRMACION = 'Regularidad registrada exitosamente';

    public const CONFIRMACION_DETALLE = 'Se actualizaron las notas, las faltas y la condicion final del alumno en la materia.';

    public const NOTAS = ['nota_p1' => 'Nota P1', 'nota_p2' => 'Nota P2', 'nota_r1' => 'Nota R1', 'nota_r2' => 'Nota R2'];

    /**
     * Paso 2: carreras "Activo".
     *
     * @return EloquentCollection<int, Carrera>
     */
    public function carreras(): EloquentCollection
    {
        return Carrera::query()->activas()->orderBy('nombre_carrera')->get();
    }

    /**
     * Paso 5: comisiones "Activo" de la carrera, ordenadas.
     *
     * @return EloquentCollection<int, Comision>
     */
    public function comisiones(?Carrera $carrera): EloquentCollection
    {
        if (! $carrera) {
            return new EloquentCollection;
        }

        return Comision::query()->activas()->with('modalidad')->where('carrera_id', $carrera->id)
            ->orderByDesc('anio_lectivo')->orderBy('anio_curso')->orderBy('codigo_comision')->get();
    }

    /**
     * Paso 8: materias de la comisión agrupadas por cuatrimestre (1er, 2do, Anual).
     *
     * @return Collection<string, Collection<int, DetComision>>
     */
    public function materiasPorCuatrimestre(Comision $comision): Collection
    {
        return $comision->detalles()->where('estado_det_comision', EstadoGeneral::Activo->value)
            ->with(['detallePlan.materia', 'detallePlan.cuatrimestre'])->get()
            ->sortBy(fn (DetComision $d) => [(int) $d->detallePlan->cuatrimestre_id, $d->detallePlan->materia->nombre_materia])
            ->groupBy(fn (DetComision $d) => (string) $d->detallePlan->cuatrimestre?->cuatrimestre);
    }

    /**
     * Paso 11: alumnos con inscripción "Activo" en la materia de la comisión.
     *
     * @return EloquentCollection<int, AlumnoComision>
     */
    public function inscriptos(DetComision $detComision): EloquentCollection
    {
        return $detComision->inscripciones()->activas()->with('alumno.usuario')->get()
            ->sortBy(fn (AlumnoComision $ac) => $ac->alumno->usuario->nombre_completo)->values();
    }

    /**
     * Pasos 22–24.
     *
     * @param  array{nota_p1: ?string, nota_p2: ?string, nota_r1: ?string, nota_r2: ?string, faltas_alumno: ?int, condicion_final: string}  $datos
     */
    public function registrar(AlumnoComision $inscripcion, array $datos): AlumnoComision
    {
        return DB::transaction(function () use ($inscripcion, $datos) {
            $inscripcion = AlumnoComision::query()->with(['alumno.usuario', 'alumno.carrera', 'detComision.comision', 'detComision.detallePlan.materia'])
                ->lockForUpdate()->findOrFail($inscripcion->id);
            if ($inscripcion->estado_alumno_comision !== EstadoAlumnoComision::Activo) {
                throw new OperacionNoPermitida(self::MENSAJE_INSCRIPCION_CADUCADA);
            }

            $condicion = CondicionCursada::from($datos['condicion_final']);
            $inscripcion->update([...$datos, 'condicion_final' => $condicion]);

            EstadoCurricular::query()->create([
                'alumno_id' => $inscripcion->alumno_id,
                'detalle_plan_estudio_id' => $inscripcion->detComision->detalle_plan_estudio_id,
                'condicion' => CondicionCurricular::from($condicion->value),
                'fecha_condicion' => today(),
                'nota_condicion' => $this->notaCondicion($inscripcion),
            ]);

            Mail::to($inscripcion->alumno->usuario)->queue(new CondicionRegistrada($inscripcion));

            return $inscripcion;
        });
    }

    /**
     * notaCondicion (E16-D2): promedio de las notas definitivas de cada parcial (el recuperatorio reemplaza a su parcial).
     */
    public function notaCondicion(AlumnoComision $inscripcion): ?float
    {
        $definitivas = collect([$inscripcion->nota_r1 ?? $inscripcion->nota_p1, $inscripcion->nota_r2 ?? $inscripcion->nota_p2])
            ->filter(fn ($nota) => $nota !== null)
            ->map(fn ($nota) => (float) $nota);

        return $definitivas->isEmpty() ? null : round($definitivas->avg(), 2);
    }

    /** Nota para mostrar: "-" si no hay; sin decimales de más y con coma ("7,5"). */
    public static function formatearNota(mixed $nota): string
    {
        if ($nota === null || $nota === '') {
            return '-';
        }

        return str_replace('.', ',', rtrim(rtrim(number_format((float) $nota, 2, '.', ''), '0'), '.'));
    }
}
