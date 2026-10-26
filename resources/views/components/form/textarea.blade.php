@props(['name', 'label' => null, 'value' => null, 'requerido' => false, 'placeholder' => null, 'ayuda' => null, 'filas' => 3, 'grupoClase' => null])
@php
    $id = \App\Support\CampoFormulario::id($name, $attributes->get('id'));
    $error = \App\Support\CampoFormulario::claveError($name);
    $describe = \App\Support\CampoFormulario::describedBy($id, $ayuda, $error, $errors);
@endphp
<x-form.grupo :id="$id" :label="$label" :error="$error" :requerido="$requerido" :ayuda="$ayuda" :class="$grupoClase">
    <textarea name="{{ $name }}" id="{{ $id }}" rows="{{ $filas }}"
              {{ $attributes->except('id')->class(['form-control', 'is-invalid' => $errors->has($error)]) }}
              @if ($placeholder) placeholder="{{ $placeholder }}" @endif
              @if ($requerido) required aria-required="true" @endif
              @if ($describe) aria-describedby="{{ $describe }}" @endif
              @error($error) aria-invalid="true" @enderror>{{ old($error, $value) }}</textarea>
</x-form.grupo>
