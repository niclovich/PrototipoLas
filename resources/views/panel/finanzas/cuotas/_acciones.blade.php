{{-- ACCIÓN de P1 (P1.png): ✎ Modificar (CU 34), solo en cuotas "Activo". --}}
@if ($fila->estado_cuota === \App\Enums\EstadoGeneral::Activo)
    <x-btn-accion :href="route('panel.cuotas.edit', $fila)" icono="fas fa-pen" texto="Modificar" color="las" permiso="editar_cuota" />
@endif
