{{-- ACCIÓN de P1 (Registrar_P1.png): 👁 y 🗑 sin CU en la tesis (deshabilitados, D4 de E4) y ✎ Modificar (CU 4). --}}
<x-btn-accion icono="fas fa-eye" texto="Ver" color="las" deshabilitado="Fuera del alcance del prototipo" />
<x-btn-accion :href="route('panel.carreras.edit', $fila)" icono="fas fa-pen" texto="Modificar" color="las" permiso="editar_carrera" />
<x-btn-accion icono="fas fa-trash-alt" texto="Dar de baja" color="las" deshabilitado="Fuera del alcance del prototipo" />
