{{-- ACCION de P1 Gestión de Cajas: "Ver" (en una caja Abierta del cajero abre el Cierre, CU 36). --}}
<x-btn-accion :href="route('panel.cajas.show', $fila)" icono="fas fa-eye" texto="Ver" color="las" :con-texto="true" permiso="listar_caja" />
