@if ($fila->estaActivo())
    <x-btn-accion :href="route('panel.usuarios.edit', $fila)" icono="fas fa-pen" texto="Modificar" permiso="editar_usuario" />
    @if ($fila->password === null)
        <x-btn-accion :action="route('panel.usuarios.reenviar-link', $fila)" icono="fas fa-envelope" texto="Reenviar enlace de contraseña"
                      permiso="crear_usuario" confirmar="Se enviará un nuevo enlace a {{ $fila->email }}." />
    @endif
    @unless ($fila->is(auth()->user()))
        <x-btn-accion :action="route('panel.usuarios.destroy', $fila)" method="DELETE" icono="fas fa-user-slash"
                      texto="Dar de baja" color="outline-danger" permiso="eliminar_usuario"
                      confirmar="{{ $fila->nombre_completo }} no podrá ingresar al sistema." />
    @endunless
@else
    <x-btn-accion :action="route('panel.usuarios.reactivar', $fila)" method="PATCH" icono="fas fa-undo"
                  texto="Reactivar" color="outline-success" permiso="eliminar_usuario"
                  confirmar="{{ $fila->nombre_completo }} podrá volver a ingresar al sistema." />
@endif
