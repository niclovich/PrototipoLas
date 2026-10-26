{{-- Formulario genérico de catálogo (alta y modificación). Variables: $catalogo, $registro, $accion, $metodo. --}}
<form action="{{ $accion }}" method="POST" novalidate>
    @csrf
    @if ($metodo !== 'POST')
        @method($metodo)
    @endif
    <div class="card card-outline card-las">
        <div class="card-body">
            <div class="row">
                @foreach ($catalogo->campos as $campo => $def)
                    <div class="col-12 col-md-6">
                        @switch($catalogo->tipo($campo))
                            @case('select')
                                <x-form.select :name="$campo" :label="$def['label']" :opciones="$catalogo->opciones($campo)"
                                               :value="$registro->{$campo}" :requerido="true" :select2="true" />
                                @break
                            @case('numero')
                                <x-form.input :name="$campo" type="number" :label="$def['label']" :value="$registro->{$campo}" :requerido="true"
                                              :min="$def['min'] ?? 0" :max="$def['max'] ?? null" mascara="entero" />
                                @break
                            @default
                                <x-form.input :name="$campo" :label="$def['label']" :value="$registro->{$campo}" :requerido="true"
                                              :maxlength="$def['max'] ?? 255" />
                        @endswitch
                    </div>
                @endforeach
            </div>
        </div>
        <div class="card-footer d-flex flex-wrap justify-content-end">
            <a href="{{ route('panel.catalogos.registros.index', $catalogo->clave) }}" class="btn btn-secondary mr-2 mb-1">Cancelar</a>
            <button type="submit" class="btn btn-las mb-1"><i class="fas fa-save" aria-hidden="true"></i> Guardar</button>
        </div>
    </div>
</form>
