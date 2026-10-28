<?php

namespace App\DataTables;

use App\Enums\EstadoCuenta;
use App\Enums\RolSistema;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Yajra\DataTables\EloquentDataTable;

/**
 * P1 Gestión de Personal: pestaña Activos (cuenta Activo) y Eliminados (cuenta Inactivo, con fecha y motivo
 * de la última baja). Filtros del wireframe: "Buscar por DNI o nombre" (buscar) y "Rol: Todos" (rol).
 */
class PersonalDataTable extends BaseDataTable
{
    protected function consulta(Request $request): Builder
    {
        $eliminados = $this->pestania($request) === self::PESTANIA_ELIMINADOS;

        return User::query()
            ->select('users.*')
            ->personal()
            ->conEstadoCuenta($eliminados ? EstadoCuenta::Inactivo : EstadoCuenta::Activo)
            ->buscar($request->input('buscar'))
            ->when(in_array($request->input('rol'), RolSistema::valoresDelPersonal(), true), fn (Builder $q) => $q->role($request->input('rol')))
            ->with($eliminados ? ['roles', 'ultimaBaja.motivo'] : ['roles'])
            // Orden fijo (el wireframe no tiene orden por columna): apellido y nombre.
            ->orderBy('apellido_usuario')
            ->orderBy('nombre_usuario');
    }

    protected function columnas(EloquentDataTable $tabla): EloquentDataTable
    {
        return $tabla
            ->addColumn('apellido_nombre', fn (User $u) => $u->nombre_completo)
            ->addColumn('rol', fn (User $u) => $u->nombreRol() ?? '—')
            ->addColumn('estado', fn (User $u) => $this->badge($u->estado_cuenta))
            ->editColumn('telefono_usuario', fn (User $u) => $u->telefono_usuario ?? '—')
            ->addColumn('fecha_alta_texto', fn (User $u) => $u->fecha_alta?->format('d/m/Y') ?? '—')
            ->addColumn('fecha_baja', fn (User $u) => $u->relationLoaded('ultimaBaja') ? ($u->ultimaBaja?->fecha_novedad?->format('d/m/Y') ?? '—') : '')
            ->addColumn('motivo_baja', fn (User $u) => $u->relationLoaded('ultimaBaja') ? ($u->ultimaBaja?->motivo?->descripcion ?? '—') : '');
    }

    protected function campos(): array
    {
        return ['id', 'dni_usuario', 'apellido_nombre', 'rol', 'email', 'telefono_usuario', 'fecha_alta_texto', 'fecha_baja', 'motivo_baja'];
    }

    protected function vistaAcciones(): string
    {
        return 'panel.personal._acciones';
    }
}
