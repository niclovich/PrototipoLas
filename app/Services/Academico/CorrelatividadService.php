<?php

namespace App\Services\Academico;

use App\Enums\CondicionCorrelativa;
use App\Models\Correlativa;
use App\Models\DetallePlanEstudio;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * CU 8 Registrar Correlatividades de Materia (Coordinador de Carrera).
 * Correlativa no tiene estado (§7-20); solo se muestran y se editan las correlativas cuya materia de la que
 * depende sigue "Activo" en el plan (E7-D10).
 */
class CorrelatividadService
{
    /** Título de la Confirmación (Correlatividades_P3.png). */
    public const CONFIRMACION_REGISTRO = 'Correlatividades registradas exitosamente';

    /** Validaciones propias (CU 8 no tiene escenarios alternativos). */
    public const MENSAJE_CORRELATIVA_INVALIDA = 'La materia correlativa debe ser del mismo plan y de un año de dictado anterior.';

    public const MENSAJE_CORRELATIVA_REPETIDA = 'La materia ya fue agregada como correlativa.';

    public const MENSAJE_CONDICION = 'Debe seleccionar la condición para cursar y para rendir.';

    /**
     * CU 8 paso 9: materias "Activo" del mismo plan con año de dictado anterior al de la materia.
     *
     * @return Collection<int, DetallePlanEstudio>
     */
    public function disponibles(DetallePlanEstudio $detalle): Collection
    {
        return DetallePlanEstudio::query()
            ->where('plan_estudio_id', $detalle->plan_estudio_id)
            ->activos()
            ->where('anio_curso_det_plan_estudio', '<', $detalle->anio_curso_det_plan_estudio)
            ->with('materia')
            ->orderBy('anio_curso_det_plan_estudio')
            ->orderBy('codigo_det_plan_estudio')
            ->get();
    }

    /**
     * Correlativas ya cargadas de la materia (P2 las muestra al abrir, diagrama de estados).
     *
     * @return Collection<int, Correlativa>
     */
    public function vigentes(DetallePlanEstudio $detalle): Collection
    {
        return $this->consultaVigentes($detalle)->with('detallePlanCorrelativa.materia')->orderBy('id')->get();
    }

    /**
     * CU 8 pasos 23–24: la tabla de P2 reemplaza las correlativas vigentes de la materia
     * (se crean las nuevas, se actualizan las condiciones y se eliminan las quitadas con 🗑).
     *
     * @param  list<array{detalle_plan_correlativa_id: int|string, p_cursar: string, p_rendir: string}>  $filas
     */
    public function registrar(DetallePlanEstudio $detalle, array $filas): void
    {
        DB::transaction(function () use ($detalle, $filas) {
            DetallePlanEstudio::query()->lockForUpdate()->findOrFail($detalle->getKey());
            $ids = array_map(fn (array $fila) => (int) $fila['detalle_plan_correlativa_id'], $filas);

            $this->consultaVigentes($detalle)->whereNotIn('detalle_plan_correlativa_id', $ids)->get()->each->delete();

            foreach ($filas as $fila) {
                Correlativa::query()->updateOrCreate(
                    ['detalle_plan_estudio_id' => $detalle->id, 'detalle_plan_correlativa_id' => (int) $fila['detalle_plan_correlativa_id']],
                    ['p_cursar' => CondicionCorrelativa::from($fila['p_cursar']), 'p_rendir' => CondicionCorrelativa::from($fila['p_rendir'])],
                );
            }
        });
    }

    /** @return list<string> Correlatividades_P3.png */
    public function lineasConfirmacion(DetallePlanEstudio $detalle): array
    {
        $detalle->loadMissing(['planEstudio.carrera', 'materia']);
        $correlativas = $this->vigentes($detalle)->map(fn (Correlativa $c) => sprintf(
            '%s (Cursar: %s · Rendir: %s)',
            $c->detallePlanCorrelativa->materia->nombre_materia,
            $c->p_cursar?->inicial() ?? '-',
            $c->p_rendir?->inicial() ?? '-',
        ))->all();

        return [
            "Carrera: {$detalle->planEstudio->carrera->nombre_carrera} · Plan: {$detalle->planEstudio->anio_plan_estudio}",
            "Materia: {$detalle->materia->nombre_materia} · Código: {$detalle->codigo_det_plan_estudio}",
            'Correlativas: '.($correlativas[0] ?? 'sin correlativas'),
            ...array_slice($correlativas, 1),
        ];
    }

    private function consultaVigentes(DetallePlanEstudio $detalle): Builder
    {
        return Correlativa::query()
            ->where('detalle_plan_estudio_id', $detalle->id)
            ->whereHas('detallePlanCorrelativa', fn (Builder $q) => $q->activos());
    }
}
