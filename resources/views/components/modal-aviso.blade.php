{{--
    Modal de aviso de los wireframes (cabecera bordó, "✕", ícono (!), mensaje y "Aceptar").
    abrir: se muestra al cargar la página (resources/js/modal-aviso.js). foco: selector que recibe el foco al cerrar.
    lista: renglones opcionales debajo del mensaje (p. ej. CU3 alt. 5.2: Nombre y Mes de las cuotas pendientes).
--}}
@props(['id', 'titulo', 'mensaje' => '', 'abrir' => false, 'foco' => null, 'lista' => []])
<div class="modal fade las-modal" id="{{ $id }}" tabindex="-1" role="alertdialog" aria-labelledby="{{ $id }}-titulo"
     aria-describedby="{{ $id }}-mensaje" aria-hidden="true" data-modal-aviso
     @if ($abrir) data-abrir @endif @if ($foco) data-foco="{{ $foco }}" @endif>
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header las-modal__cabecera">
                <h5 class="modal-title" id="{{ $id }}-titulo">{{ $titulo }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body d-flex align-items-center">
                <span class="las-modal__icono mr-3" aria-hidden="true">!</span>
                <div>
                    <p class="mb-0" id="{{ $id }}-mensaje" data-mensaje>{{ $mensaje }}</p>
                    @if ($lista)
                        <ul class="mb-0 mt-2 pl-3">
                            @foreach ($lista as $renglon)
                                <li>{{ $renglon }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-las px-5" data-dismiss="modal">Aceptar</button>
            </div>
        </div>
    </div>
</div>
