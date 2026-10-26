{{--
    Checkbox con estilo iCheck. Para listas usar name="campo[]" y value; tooltip opcional (p. ej. nombre técnico).
    Si el formulario volvió con errores, manda lo cargado (old) aunque no se haya marcado ninguna casilla.
--}}
@props(['name', 'label', 'value' => '1', 'checked' => false, 'tooltip' => null, 'grupoClase' => 'mb-2'])
@php
    $id = $attributes->get('id', \App\Support\CampoFormulario::id($name).'_'.\Illuminate\Support\Str::slug((string) $value, '_'));
    $error = \App\Support\CampoFormulario::claveError($name);
    $marcado = session()->hasOldInput()
        ? in_array((string) $value, array_map('strval', (array) old($error, [])), true)
        : $checked;
@endphp
<div class="icheck-primary icheck-las {{ $grupoClase }}" @if ($tooltip) data-toggle="tooltip" title="{{ $tooltip }}" @endif>
    <input type="checkbox" name="{{ $name }}" id="{{ $id }}" value="{{ $value }}" {{ $attributes->except('id') }} @checked($marcado)>
    <label for="{{ $id }}">{{ $label }}</label>
</div>
