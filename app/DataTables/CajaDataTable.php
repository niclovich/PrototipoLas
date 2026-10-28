<?php

namespace App\DataTables;

use App\Enums\EstadoCaja;
use App\Enums\RolSistema;
use App\Models\Caja;
use App\Support\Moneda;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Yajra\DataTables\EloquentDataTable;

/**
 * P1 Gestión de Cajas (Fig. 250): cajas del cajero logueado, de la más reciente a la más antigua, con el filtro "Estado".
 * El SUPERADMIN ve todas.
 */
class CajaDataTable extends BaseDataTable
{
    protected function consulta(Request $request): Builder
    {
        $usuario = $request->user();
        $estado = EstadoCaja::tryFrom((string) $request->input('estado'));

        return Caja::query()
            ->select('cajas.*')
            ->with('cajero')
            ->unless($usuario->tieneRol(RolSistema::SuperAdmin), fn (Builder $q) => $q->where('user_id', $usuario->id))
            ->when($estado, fn (Builder $q) => $q->where('estado_caja', $estado->value))
            ->orderByDesc('fecha_apertura')
            ->orderByDesc('id');
    }

    protected function columnas(EloquentDataTable $tabla): EloquentDataTable
    {
        return $tabla
            ->addColumn('fecha_apertura_texto', fn (Caja $c) => $c->fecha_apertura->format('d/m/Y H:i:s'))
            ->addColumn('fecha_cierre_texto', fn (Caja $c) => $c->fecha_cierre?->format('d/m/Y H:i:s') ?? '—')
            ->addColumn('cajero', fn (Caja $c) => $c->cajero?->name ?? '—')
            ->addColumn('monto_inicial_texto', fn (Caja $c) => Moneda::formatear($c->monto_inicial))
            ->addColumn('estado', fn (Caja $c) => $this->badge($c->estado_caja));
    }

    protected function campos(): array
    {
        return ['id', 'fecha_apertura_texto', 'fecha_cierre_texto', 'cajero', 'monto_inicial_texto'];
    }

    protected function vistaAcciones(): string
    {
        return 'panel.finanzas.cajas._acciones';
    }
}
