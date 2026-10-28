{{--
    Acción de P1 (P1.png): 👁 "Ver" → CU6 P2 Alumnos Inscriptos al Acta (actas "Pendiente", Preceptor),
    ✎ Modificar (CU 22, solo actas "Pendiente") y 🗑 sin CU en la tesis (deshabilitado).
--}}
@php $pendiente = $fila->estado_acta === \App\Enums\EstadoActa::Pendiente; @endphp
@if (! $pendiente)
    <x-btn-accion icono="fas fa-eye" texto="Ver" color="las" deshabilitado="Acta asentada" />
@elseif (auth()->user()->can('crear_asentamiento_acta'))
    <x-btn-accion :href="route('panel.asentamiento.show', $fila)" icono="fas fa-eye" texto="Ver" color="las" />
@else
    <x-btn-accion icono="fas fa-eye" texto="Ver" color="las" deshabilitado="No tiene permiso para asentar actas" />
@endif
@if ($pendiente)
    <x-btn-accion :href="route('panel.actas.edit', $fila)" icono="fas fa-pen" texto="Modificar" color="las" permiso="editar_acta_examen" />
@else
    <x-btn-accion icono="fas fa-pen" texto="Modificar" color="las" permiso="editar_acta_examen" deshabilitado="Acta asentada" />
@endif
<x-btn-accion icono="fas fa-trash-alt" texto="Dar de baja" color="las" deshabilitado="Fuera del alcance del prototipo" />
