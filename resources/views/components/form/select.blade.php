{{-- Select. opciones: [valor => texto]. select2: búsqueda para listas largas (resources/js/select2-init.js). --}}
@props([
    'name', 'label' => null, 'opciones' => [], 'value' => null, 'requerido' => false,
    'placeholder' => 'Seleccione...', 'ayuda' => null, 'select2' => false, 'multiple' => false, 'grupoClase' => null,
])
@php
    $id = \App\Support\CampoFormulario::id($name, $attributes->get('id'));
    $error = \App\Support\CampoFormulario::claveError($name);
    $describe = \App\Support\CampoFormulario::describedBy($id, $ayuda, $error, $errors);
    $seleccion = collect(old($error, $value))->map(fn ($v) => $v instanceof \BackedEnum ? (string) $v->value : (string) $v)->all();
@endphp
<x-form.grupo :id="$id" :label="$label" :error="$error" :requerido="$requerido" :ayuda="$ayuda" :class="$grupoClase">
    <select name="{{ $name }}" id="{{ $id }}"
            {{ $attributes->except('id')->class(['form-control', 'is-invalid' => $errors->has($error)]) }}
            @if ($select2) data-las-select2 data-placeholder="{{ $placeholder }}" @endif
            @if ($multiple) multiple @endif
            @if ($requerido) required aria-required="true" @endif
            @if ($describe) aria-describedby="{{ $describe }}" @endif
            @error($error) aria-invalid="true" @enderror>
        @unless ($multiple)
            <option value="">{{ $placeholder }}</option>
        @endunless
        @foreach ($opciones as $valor => $texto)
            <option value="{{ $valor }}" @selected(in_array((string) $valor, $seleccion, true))>{{ $texto }}</option>
        @endforeach
    </select>
</x-form.grupo>
