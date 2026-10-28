@props(['usuario', 'tamano' => 'md'])
@php
    $iniciales = collect([$usuario->nombre_usuario, $usuario->apellido_usuario])
        ->filter()
        ->map(fn ($parte) => mb_strtoupper(mb_substr($parte, 0, 1)))
        ->join('');
@endphp
<span {{ $attributes->class(['las-avatar-usuario', 'las-avatar-usuario--'.$tamano]) }} aria-hidden="true">
    {{ $iniciales ?: 'U' }}
</span>
