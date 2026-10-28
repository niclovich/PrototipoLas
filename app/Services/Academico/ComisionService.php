<?php

namespace App\Services\Academico;

use App\Enums\EstadoGeneral;
use App\Models\Carrera;
use App\Models\Comision;
use App\Models\DetallePlanEstudio;
use App\Models\DetComision;
use App\Models\Modalidad;
use App\Models\PlanEstudio;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * CU 17 Registrar datos de una nueva Comisión (Coordinador de Carrera).
 */
class ComisionService
{
    /** CU 17 alt. 10.1, 13.1, 16.1 y 19.1. */
    public const MENSAJE_ANIO_CURSO = 'El Año de Curso debe estar entre 1 y la duración de la Carrera';

    public const MENSAJE_ANIO_LECTIVO = 'El Año Lectivo no puede ser anterior al Año Actual';

    public const MENSAJE_CODIGO_EXISTENTE = 'Ya existe una Comisión con ese Código para la Carrera, Año de Curso y Año Lectivo seleccionados';

    public const MENSAJE_CAPACIDAD = 'La capacidad debe ser un número mayor a cero';

    /** E9-D3 (sin escenario en la tesis). */
    public const MENSAJE_SIN_MATERIAS = 'El Plan de Estudio activo de la Carrera no tiene materias para ese Año de Curso.';

    public const CONFIRMACION_REGISTRO = 'Comisión registrada exitosamente';

    /** @return Collection<int, Carrera> combo "Seleccionar Carrera" (spec paso 2: carreras "Activo"). */
    public function carreras(): Collection
    {
        return Carrera::query()->activas()->orderBy('nombre_carrera')->get(['id', 'nombre_carrera', 'duracion_id']);
    }

    /** @return Collection<int, Modalidad> */
    public function modalidades(): Collection
    {
        return Modalidad::query()->orderBy('modalidad')->get(['id', 'modalidad']);
    }

    public function duracion(Carrera $carrera): int
    {
        return max(1, (int) $carrera->loadMissing('duracion')->duracion?->cantidad_anios);
    }

    /**
     * Spec paso 22: carga horaria total por Año de Curso (suma de las materias "Activo" del plan activo).
     *
     * @return array<int, int> año de curso => horas
     */
    public function cargasPorAnio(Carrera $carrera): array
    {
        return $this->materiasDelPlanActivo($carrera)
            ->groupBy('anio_curso_det_plan_estudio')
            ->map(fn ($materias) => (int) $materias->sum('carga_horaria_det_plan_estudio'))
            ->all();
    }

    /**
     * CU 17 pasos 26–27: Comisión "Activo" con la carga horaria calculada y un DetComision "Activo" por cada
     * materia del plan activo del Año de Curso.
     *
     * @param  array{modalidad_id: int, codigo_comision: string, capacidad_comision: int, anio_curso: int, anio_lectivo: int, horario_comision: string}  $datos
     */
    public function registrar(Carrera $carrera, array $datos): Comision
    {
        return DB::transaction(function () use ($carrera, $datos) {
            $plan = PlanEstudio::query()->activos()->where('carrera_id', $carrera->id)->lockForUpdate()->first();
            $materias = $plan ? $this->materiasDelPlanActivo($carrera)->where('anio_curso_det_plan_estudio', $datos['anio_curso']) : collect();

            if ($materias->isEmpty()) {
                throw ValidationException::withMessages(['anio_curso' => self::MENSAJE_SIN_MATERIAS]);
            }

            $comision = new Comision([
                'codigo_comision' => $datos['codigo_comision'],
                'capacidad_comision' => $datos['capacidad_comision'],
                'horario_comision' => $datos['horario_comision'],
                'anio_curso' => $datos['anio_curso'],
                'anio_lectivo' => $datos['anio_lectivo'],
                'carga_horaria_comision' => (int) $materias->sum('carga_horaria_det_plan_estudio'),
                'estado_comision' => EstadoGeneral::Activo,
            ]);
            $comision->carrera()->associate($carrera);
            $comision->modalidad()->associate($datos['modalidad_id']);

            try {
                $comision->save();
            } catch (UniqueConstraintViolationException) {
                throw ValidationException::withMessages(['codigo_comision' => self::MENSAJE_CODIGO_EXISTENTE]);
            }

            $comision->detalles()->createMany($materias->map(fn (DetallePlanEstudio $detalle) => [
                'detalle_plan_estudio_id' => $detalle->id,
                'estado_det_comision' => EstadoGeneral::Activo,
            ])->values()->all());

            return $comision;
        });
    }

    /** @return list<string> líneas de la P3 Confirmación */
    public function lineasConfirmacion(Comision $comision): array
    {
        $comision->loadMissing('carrera')->loadCount('detalles');

        return [
            "Carrera: {$comision->carrera->nombre_carrera}",
            "Comisión: {$comision->codigo_comision} · Año de Curso: {$comision->anio_curso} · Año Lectivo: {$comision->anio_lectivo}",
            "Carga Horaria: {$comision->carga_horaria_comision} hs · Materias: {$comision->detalles_count}",
        ];
    }

    /** @return Collection<int, DetallePlanEstudio> */
    private function materiasDelPlanActivo(Carrera $carrera): Collection
    {
        return DetallePlanEstudio::query()
            ->activos()
            ->whereHas('planEstudio', fn ($plan) => $plan->activos()->where('carrera_id', $carrera->id))
            ->get(['id', 'anio_curso_det_plan_estudio', 'carga_horaria_det_plan_estudio']);
    }
}
