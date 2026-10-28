<?php

namespace App\Http\Controllers\Panel\Administracion;

use App\DataTables\AuditoriaDataTable;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

/** Módulo Auditoría (base común, skill §1): listado de activity_log con filtros y detalle de los cambios. */
class AuditoriaController extends Controller
{
    public function index(): View
    {
        return view('panel.administracion.auditoria.index', [
            'modulos' => Activity::query()->whereNotNull('log_name')->distinct()->orderBy('log_name')->pluck('log_name')->all(),
        ]);
    }

    public function datatable(Request $request, AuditoriaDataTable $tabla): JsonResponse
    {
        return $tabla->respuesta($request);
    }

    public function show(Activity $actividad): View
    {
        $actividad->load('causer');
        $nuevos = (array) $actividad->properties->get('attributes', []);
        $anteriores = (array) $actividad->properties->get('old', []);

        return view('panel.administracion.auditoria.show', [
            'actividad' => $actividad,
            'cambios' => collect(array_keys($nuevos + $anteriores))->map(fn (string $campo) => [
                'campo' => $campo,
                'anterior' => $this->texto($anteriores[$campo] ?? null),
                'nuevo' => $this->texto($nuevos[$campo] ?? null),
            ])->all(),
        ]);
    }

    private function texto(mixed $valor): string
    {
        return match (true) {
            $valor === null => '—',
            is_bool($valor) => $valor ? 'Sí' : 'No',
            is_array($valor) => (string) json_encode($valor, JSON_UNESCAPED_UNICODE),
            default => (string) $valor,
        };
    }
}
