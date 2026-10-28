{{--
    ACCIONES de P1 (P1.png): Activos = Modificar (✎) y Dar de baja (🗑) en todas las filas; en la fila propia el 🗑
    queda deshabilitado (D11; el servicio también lo bloquea). Eliminados = ver (👁, fuera de alcance: D5).
--}}
@if ($fila->estaActivo())
    <x-btn-accion :href="route('panel.personal.edit', $fila)" icono="fas fa-pen" texto="Modificar" color="las" permiso="editar_personal" />
    @can('eliminar_personal')
        @if ($fila->is(auth()->user()))
            <x-btn-accion icono="fas fa-trash-alt" texto="Dar de baja" color="las"
                          :deshabilitado="\App\Services\Usuarios\UsuarioService::MENSAJE_PROPIA_BAJA" />
        @else
            <button type="button" class="btn btn-sm btn-las" title="Dar de baja" aria-label="Dar de baja" data-toggle="tooltip" data-baja
                    data-action="{{ route('panel.personal.baja', $fila) }}" data-id="{{ $fila->id }}" data-dni="{{ $fila->dni_usuario }}"
                    data-nombre="{{ $fila->nombre_completo }}" data-rol="{{ $fila->nombreRol() }}"
                    data-alta="{{ $fila->fecha_alta?->format('d/m/Y') }}" data-estado="{{ $fila->estado_cuenta->label() }}">
                <i class="fas fa-trash-alt" aria-hidden="true"></i>
            </button>
        @endif
    @endcan
@else
    <x-btn-accion icono="fas fa-eye" texto="Ver" color="las" deshabilitado="Fuera del alcance del prototipo" />
@endif
