{{-- Encabezado bordó con el título en una caja blanca (estilo de los wireframes). El slot recibe botones. --}}
@props(['titulo', 'subtitulo' => null])
<div {{ $attributes->merge(['class' => 'las-header d-flex flex-wrap align-items-center justify-content-between']) }}>
    <h1 class="las-header__titulo">{{ $titulo }}</h1>
    @if (trim($slot) !== '')
        <div class="las-header__acciones">{{ $slot }}</div>
    @endif
</div>
@if ($subtitulo)
    <p class="text-muted mt-2 mb-0">{{ $subtitulo }}</p>
@endif
