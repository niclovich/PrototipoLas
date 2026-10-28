<?php

namespace App\Http\Controllers\Panel\Academico;

use App\Enums\CondicionCorrelativa;
use App\Http\Controllers\Concerns\ConPantallaConfirmacion;
use App\Http\Controllers\Controller;
use App\Http\Requests\Academico\RegistrarCorrelatividadesRequest;
use App\Models\Correlativa;
use App\Models\DetallePlanEstudio;
use App\Services\Academico\CorrelatividadService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/** CU 8 Registrar Correlatividades de Materia: P2 Formulario (desde "+" de P1) → P3 Confirmación. */
class CorrelatividadController extends Controller
{
    use ConPantallaConfirmacion;

    public function __construct(private readonly CorrelatividadService $correlatividades) {}

    /** CU 8 pasos 8–20: datos de la materia, correlativas ya cargadas y materias disponibles. */
    public function create(DetallePlanEstudio $detalle): View
    {
        $detalle->load(['planEstudio.carrera', 'materia']);
        $disponibles = $this->correlatividades->disponibles($detalle);

        $filas = $this->correlatividades->vigentes($detalle)->map(fn (Correlativa $c) => [
            'detalle_plan_correlativa_id' => $c->detalle_plan_correlativa_id,
            'p_cursar' => $c->p_cursar?->value,
            'p_rendir' => $c->p_rendir?->value,
        ])->all();

        return view('panel.academico.planes.correlatividades.registrar', [
            'detalle' => $detalle,
            'disponibles' => $disponibles->mapWithKeys(fn (DetallePlanEstudio $d) => [$d->id => [
                'materia' => $d->materia->nombre_materia,
                'codigo' => $d->codigo_det_plan_estudio,
            ]])->all(),
            'filas' => old('correlativas', $filas),
            'condiciones' => collect(CondicionCorrelativa::cases())->mapWithKeys(fn (CondicionCorrelativa $c) => [$c->value => [
                'opcion' => $c->opcion(),
                'celda' => $c->celda(),
            ]])->all(),
        ]);
    }

    /** CU 8 pasos 22–25. */
    public function store(RegistrarCorrelatividadesRequest $request, DetallePlanEstudio $detalle): RedirectResponse
    {
        $this->correlatividades->registrar($detalle, $request->filas());

        return $this->confirmar('panel.planes.confirmacion', [
            'titulo' => CorrelatividadService::CONFIRMACION_REGISTRO,
            'lineas' => $this->correlatividades->lineasConfirmacion($detalle),
            'carrera_id' => $detalle->planEstudio->carrera_id,
        ]);
    }
}
