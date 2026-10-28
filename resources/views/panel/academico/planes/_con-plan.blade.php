{{--
    P1 con plan registrado (Registrar_Materia_P1.png): año del plan, "Generar Reporte" (fuera de alcance), "Registrar Materia"
    (CU 6), "Filtrar" y "MATERIAS DEL PLAN". "Registrar Materia", 🗑 y "+" solo operan sobre el plan "Activo" (E6-D4).
--}}
@php
    $claveAviso = \App\Http\Controllers\Panel\Academico\PlanEstudioController::AVISO;
    $esActivo = $plan->estado_plan_estudio === \App\Enums\EstadoGeneral::Activo;
    $puedeRegistrarMateria = $esActivo && auth()->user()->can('crear_materia_plan');
@endphp
<div class="d-flex flex-wrap align-items-end justify-content-between">
    <div class="form-group mb-3 las-plan-anio">
        <label for="filtro-plan" class="text-muted">Año del Plan de Estudio</label>
        <select id="filtro-plan" name="plan" class="form-control las-select-bordo" data-plan-anio>
            @foreach ($planes as $id => $anio)
                <option value="{{ $id }}" @selected($plan->id === $id)>{{ $anio }}</option>
            @endforeach
        </select>
    </div>
    <div class="d-flex flex-wrap mb-3">
        <span class="d-inline-block mr-2 mb-2" tabindex="0" data-toggle="tooltip" title="Fuera del alcance del prototipo">
            <button type="button" class="btn btn-las las-btn-principal las-btn-deshabilitado" disabled>Generar Reporte</button>
        </span>
        @if ($puedeRegistrarMateria)
            <a href="{{ route('panel.planes.materias.create', $plan) }}" class="btn btn-las las-btn-principal mb-2">Registrar Materia</a>
        @else
            <span class="d-inline-block mb-2" tabindex="0" data-toggle="tooltip"
                  title="{{ $esActivo ? 'No tiene permiso para esta acción' : 'Solo se agregan materias al plan de estudio activo' }}">
                <button type="button" class="btn btn-las las-btn-principal las-btn-deshabilitado" disabled>Registrar Materia</button>
            </span>
        @endif
    </div>
</div>

<div class="las-filtros mb-3">
    <label for="filtro-buscar" class="las-etiqueta-filtro">Filtrar</label>
    <input type="search" id="filtro-buscar" name="buscar" class="form-control las-buscador" placeholder="Buscar por materia o código.."
           aria-label="Buscar por materia o código" autocomplete="off">
</div>

<h2 class="las-subtitulo">MATERIAS DEL PLAN</h2>
<x-datatable id="plan-materias-tabla" :url="route('panel.planes.datatable')" :params="['carrera' => $carrera->id, 'plan' => $plan->id]"
             :filtros="['#filtro-buscar']" dom="rtip" :orden="[]"
             :columnas="[
                 ['data' => 'materia', 'titulo' => 'Materia', 'orderable' => false, 'searchable' => false, 'prioridad' => 1],
                 ['data' => 'codigo', 'titulo' => 'Código', 'orderable' => false, 'searchable' => false, 'prioridad' => 3],
                 ['data' => 'regimen', 'titulo' => 'Régimen', 'orderable' => false, 'searchable' => false, 'prioridad' => 6],
                 ['data' => 'cuatrimestre', 'titulo' => 'Cuatrimestre', 'orderable' => false, 'searchable' => false, 'prioridad' => 7],
                 ['data' => 'anio', 'titulo' => 'Año', 'orderable' => false, 'searchable' => false, 'prioridad' => 5],
                 ['data' => 'carga', 'titulo' => 'Carga Hs.', 'orderable' => false, 'searchable' => false, 'prioridad' => 8],
                 ['data' => 'correlativas', 'titulo' => 'Correlativas', 'orderable' => false, 'searchable' => false, 'clase' => 'text-nowrap', 'prioridad' => 4],
                 ['data' => 'acciones', 'titulo' => 'Acciones', 'orderable' => false, 'searchable' => false, 'clase' => 'text-nowrap', 'prioridad' => 2],
             ]" />

@include('panel.academico.planes._modal-baja-materia')

{{-- CU 7 alt. 6: comisiones dictándose (al tocar 🗑 o al confirmar); Aceptar → fin del CU sobre P1. --}}
<x-modal-aviso id="modal-comisiones-dictandose" titulo="Materia con comisiones"
               :mensaje="session($claveAviso, \App\Services\Academico\DetallePlanEstudioService::MENSAJE_COMISIONES_DICTANDOSE)"
               :abrir="session()->has($claveAviso)" />
