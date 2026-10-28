<?php

namespace App\DataTables;

use App\Models\Materia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Yajra\DataTables\EloquentDataTable;

/** P1 Gestión de Materias: NOMBRE DE LA MATERIA · ESTADO · ACCIONES. Orden fijo por nombre (sin orden por columna). */
class MateriaDataTable extends BaseDataTable
{
    protected function consulta(Request $request): Builder
    {
        return Materia::query()->select('materias.*')->buscar($request->input('buscar'))->orderBy('nombre_materia');
    }

    protected function columnas(EloquentDataTable $tabla): EloquentDataTable
    {
        return $tabla->addColumn('estado', fn (Materia $m) => $this->badge($m->estado_materia));
    }

    protected function campos(): array
    {
        return ['id', 'nombre_materia'];
    }

    protected function vistaAcciones(): string
    {
        return 'panel.academico.materias._acciones';
    }
}
