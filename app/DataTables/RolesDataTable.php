<?php

namespace App\DataTables;

use App\Enums\EstadoGeneral;
use App\Models\Rol;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Yajra\DataTables\EloquentDataTable;

class RolesDataTable extends BaseDataTable
{
    protected function consulta(Request $request): Builder
    {
        $estado = $this->pestania($request) === self::PESTANIA_ELIMINADOS ? EstadoGeneral::Inactivo : EstadoGeneral::Activo;

        return Rol::query()
            ->select('roles.*')
            ->withCount(['permissions', 'users'])
            ->where('estado', $estado->value);
    }

    protected function columnas(EloquentDataTable $tabla): EloquentDataTable
    {
        return $tabla
            ->addColumn('estado', fn (Rol $r) => $this->badge($r->estado))
            ->editColumn('descripcion', fn (Rol $r) => $r->descripcion ?? '—');
    }

    protected function campos(): array
    {
        return ['id', 'name', 'descripcion', 'permissions_count', 'users_count'];
    }

    protected function vistaAcciones(): string
    {
        return 'panel.administracion.roles._acciones';
    }
}
