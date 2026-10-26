{{-- Badge por estado: recibe un Enum con label()/badge() (app/Enums) o un texto. --}}
@props(['estado'])
@php
    $esEnum = $estado instanceof \BackedEnum && method_exists($estado, 'badge');
    $color = $esEnum ? $estado->badge() : 'secondary';
    $texto = $esEnum ? $estado->label() : ($estado ?? '—');
@endphp
<span {{ $attributes->merge(['class' => "badge badge-{$color}"]) }}>{{ $texto }}</span>
