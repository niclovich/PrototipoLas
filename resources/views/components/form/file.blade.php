{{-- Adjuntar archivo (bs-custom-file-input). accept por defecto: PDF. --}}
@props(['name', 'label' => null, 'requerido' => false, 'accept' => 'application/pdf', 'ayuda' => 'Formato PDF.', 'grupoClase' => null])
@php
    $id = \App\Support\CampoFormulario::id($name, $attributes->get('id'));
    $error = \App\Support\CampoFormulario::claveError($name);
    $describe = \App\Support\CampoFormulario::describedBy($id, $ayuda, $error, $errors);
@endphp
<x-form.grupo :id="$id" :label="$label" :error="$error" :requerido="$requerido" :ayuda="$ayuda" :class="$grupoClase">
    <div class="custom-file">
        <input type="file" name="{{ $name }}" id="{{ $id }}" accept="{{ $accept }}"
               {{ $attributes->except('id')->class(['custom-file-input', 'is-invalid' => $errors->has($error)]) }}
               @if ($requerido) required aria-required="true" @endif
               @if ($describe) aria-describedby="{{ $describe }}" @endif
               @error($error) aria-invalid="true" @enderror>
        <label class="custom-file-label" for="{{ $id }}" data-browse="Examinar">Seleccionar archivo</label>
    </div>
</x-form.grupo>
