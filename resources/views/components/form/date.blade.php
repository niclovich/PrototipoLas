{{--
    Fecha con datepicker en español (Tempus Dominus, formato dd/mm/aaaa).
    El servidor recibe dd/mm/aaaa: validar con date_format:d/m/Y en el FormRequest.
--}}
@props(['name', 'label' => null, 'value' => null, 'requerido' => false, 'ayuda' => null, 'grupoClase' => null])
@php
    $id = \App\Support\CampoFormulario::id($name, $attributes->get('id'));
    $error = \App\Support\CampoFormulario::claveError($name);
    $describe = \App\Support\CampoFormulario::describedBy($id, $ayuda, $error, $errors);
    $valor = old($error, $value instanceof \DateTimeInterface ? $value->format('d/m/Y') : $value);
@endphp
<x-form.grupo :id="$id" :label="$label" :error="$error" :requerido="$requerido" :ayuda="$ayuda" :class="$grupoClase">
    <div class="input-group date" id="{{ $id }}-picker" data-target-input="nearest" data-datepicker>
        <input type="text" name="{{ $name }}" id="{{ $id }}" value="{{ $valor }}" placeholder="dd/mm/aaaa" autocomplete="off"
               data-target="#{{ $id }}-picker"
               {{ $attributes->except('id')->class(['form-control datetimepicker-input', 'is-invalid' => $errors->has($error)]) }}
               @if ($requerido) required aria-required="true" @endif
               @if ($describe) aria-describedby="{{ $describe }}" @endif
               @error($error) aria-invalid="true" @enderror>
        <div class="input-group-append" data-target="#{{ $id }}-picker" data-toggle="datetimepicker">
            <span class="input-group-text"><i class="fas fa-calendar-alt" aria-hidden="true"></i><span class="sr-only">Abrir calendario</span></span>
        </div>
    </div>
</x-form.grupo>
