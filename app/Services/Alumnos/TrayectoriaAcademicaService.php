<?php

namespace App\Services\Alumnos;

use App\Enums\CondicionCorrelativa;
use App\Enums\CondicionCurricular;
use App\Models\Alumno;
use App\Models\Correlativa;
use App\Models\DetallePlanEstudio;
use App\Models\EstadoCurricular;
use Illuminate\Support\Collection;

/**
 * Trayectoria del alumno a partir del historial de EstadoCurricular (CU3, CU5 y CU6).
 * Una correlativa "Regular" se cumple con Regular o Aprobado; "Aprobada", solo con Aprobado.
 */
class TrayectoriaAcademicaService
{
    /**
     * Condiciones registradas por materia del plan (todo el historial).
     *
     * @return Collection<int, Collection<int, CondicionCurricular>>
     */
    public function condiciones(Alumno $alumno): Collection
    {
        return EstadoCurricular::query()->where('alumno_id', $alumno->id)->get(['detalle_plan_estudio_id', 'condicion'])
            ->groupBy('detalle_plan_estudio_id')
            ->map(fn (Collection $estados) => $estados->pluck('condicion')->unique()->values());
    }

    /** @param  Collection<int, Collection<int, CondicionCurricular>>  $condiciones */
    public function tiene(Collection $condiciones, int $detallePlanId, CondicionCurricular $condicion): bool
    {
        return $condiciones->get($detallePlanId, collect())->contains($condicion);
    }

    /** @param  Collection<int, Collection<int, CondicionCurricular>>  $condiciones */
    public function cumple(Collection $condiciones, int $detallePlanId, CondicionCorrelativa $exigida): bool
    {
        return $this->tiene($condiciones, $detallePlanId, CondicionCurricular::Aprobado)
            || ($exigida === CondicionCorrelativa::Regular && $this->tiene($condiciones, $detallePlanId, CondicionCurricular::Regular));
    }

    /**
     * Correlativas de la materia que el alumno no cumple, "para cursar" (CU3) o "para rendir" (CU5).
     *
     * @param  Collection<int, Collection<int, CondicionCurricular>>  $condiciones
     * @return Collection<int, Correlativa>
     */
    public function correlativasInfringidas(DetallePlanEstudio $detalle, Collection $condiciones, string $columna): Collection
    {
        $detalle->loadMissing('correlativas.detallePlanCorrelativa.materia');

        return $detalle->correlativas
            ->filter(fn (Correlativa $c) => $c->{$columna} !== null
                && ! $this->cumple($condiciones, (int) $c->detalle_plan_correlativa_id, $c->{$columna}))
            ->values();
    }

    /** CU6 aclaración p. 74: todas las materias "Activo" del plan con un EstadoCurricular "Aprobado". */
    public function planCompleto(Alumno $alumno): bool
    {
        $materias = DetallePlanEstudio::query()->activos()->where('plan_estudio_id', $alumno->plan_estudio_id)->pluck('id');
        $aprobadas = EstadoCurricular::query()->where('alumno_id', $alumno->id)
            ->where('condicion', CondicionCurricular::Aprobado->value)
            ->distinct()->pluck('detalle_plan_estudio_id');

        return $materias->isNotEmpty() && $materias->diff($aprobadas)->isEmpty();
    }
}
