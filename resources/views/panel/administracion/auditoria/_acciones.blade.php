{{-- Acción de cada registro de Auditoría: ver el detalle de los cambios (permiso ver_auditoria). --}}
<x-btn-accion :href="route('panel.auditoria.show', $fila->id)" icono="fas fa-eye" texto="Ver cambios" color="las" permiso="ver_auditoria" />
