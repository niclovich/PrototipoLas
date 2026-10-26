{{-- Envoltorio común de los campos: label (con * si es obligatorio), campo, ayuda y error debajo. --}}
@props(['id', 'label' => null, 'error', 'requerido' => false, 'ayuda' => null])
<div {{ $attributes->merge(['class' => 'form-group']) }}>
    @if ($label)
        <label for="{{ $id }}">{{ $label }}@if ($requerido) <span class="text-danger" aria-hidden="true">*</span>@endif</label>
    @endif
    {{ $slot }}
    @if ($ayuda)
        <small id="{{ $id }}-ayuda" class="form-text text-muted">{{ $ayuda }}</small>
    @endif
    @error($error)
        <span class="invalid-feedback d-block" role="alert" id="{{ $id }}-error">{{ $message }}</span>
    @enderror
</div>
