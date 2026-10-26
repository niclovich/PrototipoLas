@if ($fila->estado === \App\Enums\EstadoGeneral::Activo)
    <x-btn-accion :href="route('panel.roles.edit', $fila)" icono="fas fa-pen" texto="Modificar permisos" permiso="editar_rol" />
    @if ($fila->esDelSistema())
        <x-btn-accion icono="fas fa-ban" texto="Dar de baja" permiso="eliminar_rol" deshabilitado="Los roles de la tesis no se pueden dar de baja" />
    @else
        <x-btn-accion :action="route('panel.roles.destroy', $fila)" method="DELETE" icono="fas fa-ban"
                      texto="Dar de baja" color="outline-danger" permiso="eliminar_rol"
                      confirmar="El rol {{ $fila->name }} dejará de poder asignarse." />
    @endif
@else
    <x-btn-accion :action="route('panel.roles.reactivar', $fila)" method="PATCH" icono="fas fa-undo"
                  texto="Reactivar" color="outline-success" permiso="eliminar_rol"
                  confirmar="El rol {{ $fila->name }} volverá a poder asignarse." />
@endif
