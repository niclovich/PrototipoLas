{{-- Tarjeta "1 · Seleccionar …" con el combo centrado: al elegir, la página se recarga con ?comision_id (estados: "se actualiza la tabla"). --}}
<div class="card las-tarjeta mb-4">
    <div class="card-body">
        <h3 class="las-subtitulo las-subtitulo--tarjeta">{{ $titulo }}</h3>
        <form method="GET" action="{{ $action }}" class="las-combo-centrado">
            <label for="comision_id" class="sr-only">{{ $titulo }}</label>
            <select name="comision_id" id="comision_id" class="form-control" data-autoenviar>
                <option value="">Seleccione una comisión</option>
                @foreach ($opciones as $id => $texto)
                    <option value="{{ $id }}" @selected($seleccionada?->id === $id)>{{ $texto }}</option>
                @endforeach
            </select>
            <noscript><button type="submit" class="btn btn-las mt-2">Ver materias</button></noscript>
        </form>
    </div>
</div>
