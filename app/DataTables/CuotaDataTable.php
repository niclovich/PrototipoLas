<?php

namespace App\DataTables;

use App\Models\Cuota;
use App\Support\Moneda;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Yajra\DataTables\EloquentDataTable;

/**
 * P1 Gestión de Cuotas: cuotas de la carrera y el año lectivo elegidos. MES · AÑO · MONTO · ESTADO · ACCIÓN,
 * ordenadas por Mes (id = orden), sin orden por columna.
 */
class CuotaDataTable extends BaseDataTable
{
    protected function consulta(Request $request): Builder
    {
        return Cuota::query()
            ->select('cuotas.*')
            ->with('mes')
            ->where('carrera_id', $request->integer('carrera'))
            ->where('anio', $request->integer('anio'))
            ->orderBy('mes_id')
            ->orderBy('id');
    }

    protected function columnas(EloquentDataTable $tabla): EloquentDataTable
    {
        return $tabla
            ->addColumn('mes', fn (Cuota $c) => $c->mes?->descripcion ?? '—')
            ->addColumn('monto_texto', fn (Cuota $c) => Moneda::formatear($c->monto))
            ->addColumn('estado', fn (Cuota $c) => $this->badge($c->estado_cuota));
    }

    protected function campos(): array
    {
        return ['id', 'mes', 'anio', 'monto_texto'];
    }

    protected function vistaAcciones(): string
    {
        return 'panel.finanzas.cuotas._acciones';
    }
}
