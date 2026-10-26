{{--
    Botón de acción con ícono + tooltip, visible solo si el usuario tiene el permiso.
    - href: enlace.
    - action (+ method): formulario con CSRF; confirmar = texto de SweetAlert2 antes de enviar.
    - modal: id de un <x-modal-confirmar>; el botón le pasa su action.
    - deshabilitado: true o el texto del tooltip (p. ej. "Fuera del alcance del prototipo").
--}}
@props([
    'icono',
    'texto',
    'href' => null,
    'action' => null,
    'method' => 'POST',
    'color' => 'outline-secondary',
    'confirmar' => null,
    'modal' => null,
    'permiso' => null,
    'conTexto' => false,
    'deshabilitado' => false,
    'tamanio' => 'sm',
])
@php
    $clases = "btn btn-{$tamanio} btn-{$color}";
    $visible = $permiso === null || auth()->user()?->can($permiso);
    $tooltip = is_string($deshabilitado) ? $deshabilitado : $texto;
@endphp
@if ($visible)
    @if ($deshabilitado)
        <span class="d-inline-block" tabindex="0" data-toggle="tooltip" title="{{ $tooltip }}">
            <button type="button" class="{{ $clases }} las-btn-deshabilitado" disabled aria-label="{{ $tooltip }}">
                <i class="{{ $icono }}" aria-hidden="true"></i>@if ($conTexto) {{ $texto }}@endif
            </button>
        </span>
    @elseif ($modal)
        <button type="button" {{ $attributes->merge(['class' => $clases]) }} data-toggle="modal" data-target="#{{ $modal }}"
                data-action="{{ $action }}" title="{{ $texto }}" aria-label="{{ $texto }}">
            <i class="{{ $icono }}" aria-hidden="true"></i>@if ($conTexto) {{ $texto }}@endif
        </button>
    @elseif ($action)
        <form action="{{ $action }}" method="POST" class="d-inline" @if ($confirmar) data-confirm="{{ $confirmar }}" @endif>
            @csrf
            @if (strtoupper($method) !== 'POST')
                @method($method)
            @endif
            <button type="submit" {{ $attributes->merge(['class' => $clases]) }} data-toggle="tooltip" title="{{ $texto }}" aria-label="{{ $texto }}">
                <i class="{{ $icono }}" aria-hidden="true"></i>@if ($conTexto) {{ $texto }}@endif
            </button>
        </form>
    @else
        <a href="{{ $href }}" {{ $attributes->merge(['class' => $clases]) }} data-toggle="tooltip" title="{{ $texto }}" aria-label="{{ $texto }}">
            <i class="{{ $icono }}" aria-hidden="true"></i>@if ($conTexto) {{ $texto }}@endif
        </a>
    @endif
@endif
