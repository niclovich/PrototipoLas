{{-- ACCIONES de P1 (P1.png): ✎ Modificar (CU 2) y 🗑 sin CU en la tesis (deshabilitado, D5 de E5). --}}
<x-btn-accion :href="route('panel.materias.edit', $fila)" icono="fas fa-pen" texto="Modificar" color="las" permiso="editar_materia" />
<x-btn-accion icono="fas fa-trash-alt" texto="Dar de baja" color="las" deshabilitado="Fuera del alcance del prototipo" />
