<?php

namespace App\DataTables;

use App\Enums\EstadoCuenta;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Yajra\DataTables\EloquentDataTable;

class UsuariosDataTable extends BaseDataTable
{
    protected function consulta(Request $request): Builder
    {
        $estado = $this->pestania($request) === self::PESTANIA_ELIMINADOS ? EstadoCuenta::Inactivo : EstadoCuenta::Activo;

        return User::query()
            ->select('users.*')
            ->with('roles')
            ->gestionablesDesdeUsuarios()
            ->conEstadoCuenta($estado);
    }

    protected function columnas(EloquentDataTable $tabla): EloquentDataTable
    {
        return $tabla
            ->addColumn('apellido_nombre', fn (User $u) => $u->nombre_completo)
            ->addColumn('rol', fn (User $u) => $u->nombreRol() ?? '—')
            ->addColumn('estado', fn (User $u) => $this->badge($u->estado_cuenta))
            ->filterColumn('apellido_nombre', fn (Builder $q, string $buscar) => $q->where(
                fn (Builder $w) => $w->where('apellido_usuario', 'like', "%{$buscar}%")->orWhere('nombre_usuario', 'like', "%{$buscar}%")
            ))
            ->filterColumn('rol', fn (Builder $q, string $buscar) => $q->whereHas('roles', fn (Builder $r) => $r->where('name', 'like', "%{$buscar}%")))
            ->orderColumn('apellido_nombre', fn (Builder $q, string $orden) => $q->orderBy('apellido_usuario', $orden)->orderBy('nombre_usuario', $orden));
    }

    protected function campos(): array
    {
        return ['id', 'apellido_nombre', 'dni_usuario', 'email', 'rol'];
    }

    protected function vistaAcciones(): string
    {
        return 'panel.administracion.usuarios._acciones';
    }
}
