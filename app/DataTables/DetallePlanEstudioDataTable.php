<?php

namespace App\DataTables;

use App\Models\DetallePlanEstudio;
use App\Models\PlanEstudio;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Yajra\DataTables\EloquentDataTable;

/**
 * P1 Gestión de Plan de Estudio, tabla "MATERIAS DEL PLAN" (CU 5–8): Materia · Código · Régimen · Cuatrimestre · Año ·
 * Carga Hs. · Correlativas · Acciones. Solo detalles "Activo" del plan elegido (E7-D5); orden fijo, sin orden por columna.
 */
class DetallePlanEstudioDataTable extends BaseDataTable
{
    private ?PlanEstudio $plan = null;

    protected function consulta(Request $request): Builder
    {
        // Un plan que no es de la carrera elegida no lista nada.
        $this->plan = PlanEstudio::query()->with('carrera')
            ->where('carrera_id', (int) $request->input('carrera'))
            ->find((int) $request->input('plan'));

        return DetallePlanEstudio::query()
            ->select('detalles_plan_estudio.*')
            ->where('plan_estudio_id', $this->plan?->id)
            ->activos()
            ->with(['materia', 'regimen', 'cuatrimestre'])
            ->withExists(['detComisiones as tiene_comisiones_dictandose' => fn (Builder $q) => $q->dictandose()])
            ->buscar($request->input('buscar'))
            ->orderBy('anio_curso_det_plan_estudio')
            ->orderBy('cuatrimestre_id')
            ->orderBy('codigo_det_plan_estudio');
    }

    protected function columnas(EloquentDataTable $tabla): EloquentDataTable
    {
        return $tabla
            ->addColumn('materia', fn (DetallePlanEstudio $d) => $d->materia->nombre_materia)
            ->addColumn('codigo', fn (DetallePlanEstudio $d) => $d->codigo_det_plan_estudio)
            ->addColumn('regimen', fn (DetallePlanEstudio $d) => $d->regimen->regimen)
            ->addColumn('cuatrimestre', fn (DetallePlanEstudio $d) => $d->cuatrimestre->abreviatura)
            ->addColumn('anio', fn (DetallePlanEstudio $d) => $d->anio_curso_det_plan_estudio)
            ->addColumn('carga', fn (DetallePlanEstudio $d) => "{$d->carga_horaria_det_plan_estudio} hs")
            ->addColumn('correlativas', fn (DetallePlanEstudio $d) => view('panel.academico.planes._correlativas', [
                'fila' => $d, ...$this->datosAcciones($d),
            ])->render());
    }

    protected function campos(): array
    {
        return ['id', 'materia', 'codigo', 'regimen', 'cuatrimestre', 'anio', 'carga', 'correlativas'];
    }

    protected function columnasHtml(): array
    {
        return ['correlativas'];
    }

    protected function vistaAcciones(): string
    {
        return 'panel.academico.planes._acciones';
    }

    protected function datosAcciones(Model $fila): array
    {
        return ['plan' => $this->plan];
    }
}
