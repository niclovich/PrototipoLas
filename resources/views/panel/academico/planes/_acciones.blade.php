{{--
    Acciones de P1 (Registrar_Materia_P1.png): 👁 y ✎ sin CU (E6-D5) y 🗑 Dar de Baja (CU 7, modal Baja_Materia_P2.png).
    Variables: $fila (DetallePlanEstudio con materia, regimen y tiene_comisiones_dictandose), $plan (con carrera).
--}}
@php
    $esActivo = $plan->estado_plan_estudio === \App\Enums\EstadoGeneral::Activo;
    $puedeBaja = $esActivo && auth()->user()->can('eliminar_materia_plan');
    $datosBaja = $puedeBaja ? [
        'action' => route('panel.planes.materias.baja', $fila->id),
        'comisiones' => (bool) $fila->tiene_comisiones_dictandose,
        'carrera' => $plan->carrera->nombre_carrera,
        'plan' => $plan->anio_plan_estudio,
        'materia' => $fila->materia->nombre_materia,
        'codigo' => $fila->codigo_det_plan_estudio,
        'regimen' => $fila->regimen->regimen,
        'anio' => $fila->anio_curso_det_plan_estudio,
        'carga' => $fila->carga_horaria_det_plan_estudio.' hs',
    ] : null;
@endphp
<x-btn-accion icono="fas fa-eye" texto="Ver" color="las" deshabilitado="Fuera del alcance del prototipo" />
<x-btn-accion icono="fas fa-pen" texto="Modificar" color="las" deshabilitado="Fuera del alcance del prototipo" />
@if ($puedeBaja)
    <button type="button" class="btn btn-sm btn-las" title="Dar de Baja" aria-label="Dar de Baja" data-toggle="tooltip"
            data-baja-materia="{{ json_encode($datosBaja) }}">
        <i class="fas fa-trash-alt" aria-hidden="true"></i>
    </button>
@else
    <x-btn-accion icono="fas fa-trash-alt" texto="Dar de Baja" color="las"
                  :deshabilitado="$esActivo ? 'No tiene permiso para esta acción' : 'Solo en el plan de estudio activo'" />
@endif
