{{--
    Confirmacion compartida de los casos de uso. Mantiene el contenido y la navegacion de cada
    wireframe, pero lo presenta como un modal de exito que se abre automaticamente.
    El slot principal admite detalle adicional; el slot "acciones" contiene acciones secundarias.
--}}
@props([
    'id' => 'confirmacion-exitosa',
    'titulo' => 'Confirmación',
    'mensaje' => null,
    'lineas' => [],
    'tituloLineas' => null,
    'urlAceptar',
    'textoBoton' => 'Aceptar',
])

<div class="modal fade las-modal-confirmacion" id="{{ $id }}" tabindex="-1" role="dialog"
     aria-modal="true" aria-labelledby="{{ $id }}-titulo" aria-describedby="{{ $id }}-contenido"
     data-modal-confirmacion data-abrir data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
        <div class="modal-content las-modal-confirmacion__contenido">
            <div class="modal-body text-center px-4 px-md-5 pt-5 pb-4" id="{{ $id }}-contenido">
                <span class="las-confirmacion__icono mb-3" aria-hidden="true"><i class="fas fa-check"></i></span>
                <h2 class="h3 font-weight-bold mb-3" id="{{ $id }}-titulo">{{ $titulo }}</h2>

                @if ($mensaje)
                    <p class="las-modal-confirmacion__mensaje mb-3">{{ $mensaje }}</p>
                @endif

                @if ($lineas && $tituloLineas)
                    <div class="card las-tarjeta las-modal-confirmacion__detalle text-left mx-auto mb-4">
                        <div class="card-body">
                            <h3 class="las-subtitulo las-subtitulo--tarjeta mt-0">{{ $tituloLineas }}</h3>
                            @foreach ($lineas as $linea)
                                <div>{{ $linea }}</div>
                            @endforeach
                        </div>
                    </div>
                @elseif ($lineas)
                    <div class="las-confirmacion__lineas mb-4">
                        @foreach ($lineas as $linea)
                            <div>{{ $linea }}</div>
                        @endforeach
                    </div>
                @endif

                @if ($slot->isNotEmpty())
                    <div class="las-modal-confirmacion__complemento">
                        {{ $slot }}
                    </div>
                @endif
            </div>

            <div class="modal-footer las-modal-confirmacion__acciones justify-content-center border-0 px-4 px-md-5 pb-5 pt-0">
                @isset($acciones)
                    {{ $acciones }}
                @endisset
                <a href="{{ $urlAceptar }}" class="btn btn-las las-btn-principal px-5"
                   data-confirmacion-aceptar>{{ $textoBoton }}</a>
            </div>
        </div>
    </div>
</div>

<noscript>
    <div class="alert alert-success" role="status">
        <strong>{{ $titulo }}</strong>
        @if ($mensaje) {{ $mensaje }} @endif
        <a href="{{ $urlAceptar }}" class="alert-link ml-2">{{ $textoBoton }}</a>
    </div>
</noscript>
