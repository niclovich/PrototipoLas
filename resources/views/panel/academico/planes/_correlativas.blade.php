{{-- Columna Correlativas de P1: 👁 sin CU (E6-D5) y "+" Registrar Correlatividades (CU 8). Variables: $fila, $plan. --}}
@php
    $esActivo = $plan->estado_plan_estudio === \App\Enums\EstadoGeneral::Activo;
    $puede = $esActivo && auth()->user()->can('crear_correlativa');
@endphp
<x-btn-accion icono="fas fa-eye" texto="Ver correlativas" color="las" deshabilitado="Fuera del alcance del prototipo" />
<x-btn-accion :href="route('panel.planes.correlatividades.create', $fila->id)" icono="fas fa-plus" texto="Registrar Correlatividades" color="las"
              :deshabilitado="$puede ? false : ($esActivo ? 'No tiene permiso para esta acción' : 'Solo en el plan de estudio activo')" />
