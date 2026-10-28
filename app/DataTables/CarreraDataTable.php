<?php

namespace App\DataTables;

use App\Models\Carrera;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Yajra\DataTables\EloquentDataTable;

/**
 * P1 Gestión de Carreras: CARRERA · MODALIDAD · DURACIÓN · RESOLUCIÓN · ESTADO · ACCIÓN, con el filtro "Buscar..".
 * Sin orden por columna (el wireframe no lo tiene): orden fijo por nombre.
 */
class CarreraDataTable extends BaseDataTable
{
    protected function consulta(Request $request): Builder
    {
        return Carrera::query()
            ->select('carreras.*')
            ->with(['modalidad', 'duracion'])
            ->buscar($request->input('buscar'))
            ->orderBy('nombre_carrera');
    }

    protected function columnas(EloquentDataTable $tabla): EloquentDataTable
    {
        return $tabla
            ->addColumn('modalidad', fn (Carrera $c) => $c->modalidad?->modalidad ?? '—')
            ->addColumn('duracion', fn (Carrera $c) => $c->duracion?->duracion ?? '—')
            ->addColumn('resolucion', fn (Carrera $c) => $c->resolucion_texto)
            ->addColumn('estado', fn (Carrera $c) => $this->badge($c->estado_carrera));
    }

    protected function campos(): array
    {
        return ['id', 'nombre_carrera', 'modalidad', 'duracion', 'resolucion'];
    }

    protected function vistaAcciones(): string
    {
        return 'panel.academico.carreras._acciones';
    }
}
