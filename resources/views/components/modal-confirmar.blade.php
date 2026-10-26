{{-- Modal de confirmación genérico. El botón que lo abre (<x-btn-accion :modal="...">) define la URL del formulario (resources/js/confirmar.js). --}}
@props(['id', 'titulo', 'mensaje', 'textoBoton' => 'Confirmar', 'color' => 'danger', 'method' => 'POST'])
<div class="modal fade" id="{{ $id }}" tabindex="-1" role="dialog" aria-labelledby="{{ $id }}-titulo" aria-hidden="true" data-modal-confirmar>
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form method="POST" action="#" class="modal-content">
            @csrf
            @if (strtoupper($method) !== 'POST')
                @method($method)
            @endif
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $id }}-titulo">{{ $titulo }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">{{ $mensaje }}</p>
                {{ $slot }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-{{ $color }}">{{ $textoBoton }}</button>
            </div>
        </form>
    </div>
</div>
