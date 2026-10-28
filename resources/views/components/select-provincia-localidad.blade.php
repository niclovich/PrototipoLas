{{-- Provincia y Localidad dependientes. urlLocalidades contiene __ID__ en lugar del id de la Provincia. --}}
<div class="row" data-provincia-localidad data-url="{{ $urlLocalidades }}">
    <div class="{{ $columna }}">
        <x-form.select name="provincia_id" label="Provincia" :opciones="$provincias" :value="$provinciaId"
                       placeholder="Seleccione una provincia" :requerido="$requerido" :select2="true" data-provincia />
    </div>
    <div class="{{ $columna }}">
        <x-form.select name="localidad_id" label="Localidad" :opciones="$localidades" :value="$localidad"
                       placeholder="Seleccione una localidad" :requerido="$requerido" :select2="true" data-localidad />
    </div>
    {{ $slot }}
</div>
