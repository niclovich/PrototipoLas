<?php

namespace App\DataTables;

use App\Models\Comision;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Yajra\DataTables\EloquentDataTable;

/**
 * P1 Gestión de Comisiones: comisiones de la carrera elegida en "Seleccionar Carrera".
 * CÓDIGO · AÑO CURSO · AÑO LECTIVO · HORARIO · CAPACIDAD · MODALIDAD · ESTADO · ACCIONES, sin orden por columna.
 */
class ComisionDataTable extends BaseDataTable
{
    protected function consulta(Request $request): Builder
    {
        return Comision::query()
            ->select('comisiones.*')
            ->with('modalidad')
            ->where('carrera_id', $request->integer('carrera'))
            ->orderByDesc('anio_lectivo')
            ->orderBy('anio_curso')
            ->orderBy('codigo_comision');
    }

    protected function columnas(EloquentDataTable $tabla): EloquentDataTable
    {
        return $tabla
            ->addColumn('modalidad', fn (Comision $c) => $c->modalidad?->modalidad ?? '—')
            ->addColumn('estado', fn (Comision $c) => $this->badge($c->estado_comision));
    }

    protected function campos(): array
    {
        return ['id', 'codigo_comision', 'anio_curso', 'anio_lectivo', 'horario_comision', 'capacidad_comision', 'modalidad'];
    }

    protected function vistaAcciones(): string
    {
        return 'panel.academico.comisiones._acciones';
    }
}
