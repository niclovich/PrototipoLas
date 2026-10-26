@if ($fila->trashed())
    <x-btn-accion :action="route('panel.catalogos.registros.reactivar', [$catalogo->clave, $fila->id])" method="PATCH" icono="fas fa-undo"
                  texto="Reactivar" color="outline-success" permiso="eliminar_catalogo" confirmar="El registro volverá a estar disponible en los formularios." />
@else
    <x-btn-accion :href="route('panel.catalogos.registros.edit', [$catalogo->clave, $fila->id])" icono="fas fa-pen" texto="Modificar" permiso="editar_catalogo" />
    <x-btn-accion :action="route('panel.catalogos.registros.destroy', [$catalogo->clave, $fila->id])" method="DELETE" icono="fas fa-ban"
                  texto="Dar de baja" color="outline-danger" permiso="eliminar_catalogo"
                  confirmar="El registro dejará de ofrecerse en los formularios. Los datos que ya lo usan no cambian." />
@endif
