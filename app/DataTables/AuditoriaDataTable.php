<?php

namespace App\DataTables;

use App\Support\EventoAuditoria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\EloquentDataTable;

/**
 * Auditoría (base común, SUPERADMIN): registros de activity_log con filtros por texto (usuario o módulo),
 * módulo, evento y rango de fechas. Del más reciente al más antiguo.
 */
class AuditoriaDataTable extends BaseDataTable
{
    protected function consulta(Request $request): Builder
    {
        $buscar = trim((string) $request->input('buscar'));

        return Activity::query()
            ->select('activity_log.*')
            ->with('causer')
            ->when($request->filled('modulo'), fn (Builder $q) => $q->where('log_name', $request->input('modulo')))
            ->when($request->filled('evento'), fn (Builder $q) => $q->where('event', $request->input('evento')))
            ->when($this->fecha($request->input('desde')), fn (Builder $q, Carbon $d) => $q->where('created_at', '>=', $d->startOfDay()))
            ->when($this->fecha($request->input('hasta')), fn (Builder $q, Carbon $h) => $q->where('created_at', '<=', $h->endOfDay()))
            ->when($buscar !== '', fn (Builder $q) => $q->where(fn (Builder $w) => $w
                ->where('log_name', 'like', "%{$buscar}%")
                ->orWhere('description', 'like', "%{$buscar}%")
                ->orWhereHasMorph('causer', '*', fn (Builder $u) => $u
                    ->where('email', 'like', "%{$buscar}%")
                    ->orWhere('apellido_usuario', 'like', "%{$buscar}%")
                    ->orWhere('nombre_usuario', 'like', "%{$buscar}%"))))
            ->orderByDesc('id');
    }

    protected function columnas(EloquentDataTable $tabla): EloquentDataTable
    {
        return $tabla
            ->addColumn('fecha', fn (Activity $a) => $a->created_at?->format('d/m/Y H:i'))
            ->addColumn('usuario', fn (Activity $a) => $a->causer?->nombre_completo ?? 'Sistema')
            ->addColumn('modulo', fn (Activity $a) => $a->log_name)
            ->addColumn('evento', fn (Activity $a) => EventoAuditoria::etiqueta($a->event ?? $a->description))
            ->addColumn('registro', fn (Activity $a) => $a->subject_id ? '#'.$a->subject_id : '—');
    }

    protected function campos(): array
    {
        return ['id', 'fecha', 'usuario', 'modulo', 'evento', 'registro'];
    }

    protected function vistaAcciones(): string
    {
        return 'panel.administracion.auditoria._acciones';
    }

    private function fecha(mixed $valor): ?Carbon
    {
        try {
            return filled($valor) ? Carbon::createFromFormat('d/m/Y', (string) $valor) : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
