{{-- Campo de texto. mascara: dni | monto | entero (resources/js/mascaras.js). --}}
@props([
    'name', 'label' => null, 'type' => 'text', 'value' => null, 'requerido' => false,
    'placeholder' => null, 'ayuda' => null, 'mascara' => null, 'grupoClase' => null,
])
@php
    $id = \App\Support\CampoFormulario::id($name, $attributes->get('id'));
    $error = \App\Support\CampoFormulario::claveError($name);
    $describe = \App\Support\CampoFormulario::describedBy($id, $ayuda, $error, $errors);
    $valor = $type === 'password' ? null : old($error, $value);
@endphp
<x-form.grupo :id="$id" :label="$label" :error="$error" :requerido="$requerido" :ayuda="$ayuda" :class="$grupoClase">
    <input type="{{ $type }}" name="{{ $name }}" id="{{ $id }}" value="{{ $valor }}"
           {{ $attributes->except('id')->class(['form-control', 'is-invalid' => $errors->has($error)]) }}
           @if ($placeholder) placeholder="{{ $placeholder }}" @endif
           @if ($requerido) required aria-required="true" @endif
           @if ($mascara) data-mascara="{{ $mascara }}" @endif
           @if ($describe) aria-describedby="{{ $describe }}" @endif
           @error($error) aria-invalid="true" @enderror>
</x-form.grupo>
