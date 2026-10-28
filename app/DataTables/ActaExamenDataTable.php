<?php

namespace App\DataTables;

use App\Enums\Asentada;
use App\Models\ActaExamen;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Yajra\DataTables\EloquentDataTable;

/**
 * P1 Gestión de Actas Examen: actas de la carrera elegida, con "Buscar.." y "Asentada" (No/Si).
 * Nro Acta · Materia · Modalidad · Llamado · Tipo Acta · Fecha · Asentada · Acción; orden fijo por Nro Acta desc.
 */
class ActaExamenDataTable extends BaseDataTable
{
    protected function consulta(Request $request): Builder
    {
        $asentada = Asentada::tryFrom((string) $request->input('asentada'));

        return ActaExamen::query()
            ->select('actas_examen.*')
            ->with(['detallePlan.materia', 'modalidad', 'llamado', 'tipoActa'])
            ->whereHas('detallePlan.planEstudio', fn (Builder $plan) => $plan->where('carrera_id', $request->integer('carrera')))
            ->when($asentada, fn (Builder $q) => $q->where('asentada', $asentada->value))
            ->buscarPalabras($request->input('buscar'), ['nro_acta'], [
                'detallePlan.materia' => ['nombre_materia'],
                'llamado' => ['llamado'],
                'tipoActa' => ['descripcion'],
                'modalidad' => ['modalidad'],
            ])
            ->orderByDesc('nro_acta');
    }

    protected function columnas(EloquentDataTable $tabla): EloquentDataTable
    {
        return $tabla
            ->addColumn('nro', fn (ActaExamen $a) => $a->nro_acta_texto)
            ->addColumn('materia', fn (ActaExamen $a) => $a->detallePlan?->materia?->nombre_materia ?? '—')
            ->addColumn('modalidad', fn (ActaExamen $a) => $a->modalidad?->modalidad ?? '—')
            ->addColumn('llamado', fn (ActaExamen $a) => $a->llamado?->llamado ?? '—')
            ->addColumn('tipo', fn (ActaExamen $a) => $a->tipoActa?->descripcion ?? '—')
            ->addColumn('fecha', fn (ActaExamen $a) => $a->fecha_acta->format('d/m/Y'))
            ->addColumn('asentada_texto', fn (ActaExamen $a) => $a->asentada->label());
    }

    protected function campos(): array
    {
        return ['id', 'nro', 'materia', 'modalidad', 'llamado', 'tipo', 'fecha', 'asentada_texto'];
    }

    protected function vistaAcciones(): string
    {
        return 'panel.examenes.actas._acciones';
    }
}
