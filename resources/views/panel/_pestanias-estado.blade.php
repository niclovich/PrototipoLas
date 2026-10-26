{{--
    Patrón index (pantallas sin wireframe): pestañas Activos / Eliminados, cada una con su DataTable server-side.
    Variables: $id (prefijo), $url (ruta datatable), $columnas, $orden (opcional).
--}}
<div class="card card-outline card-las card-tabs">
    <div class="card-header p-0 pt-1 border-bottom-0">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="{{ $id }}-tab-activos" data-toggle="tab" href="#{{ $id }}-activos" role="tab" aria-controls="{{ $id }}-activos" aria-selected="true">Activos</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="{{ $id }}-tab-eliminados" data-toggle="tab" href="#{{ $id }}-eliminados" role="tab" aria-controls="{{ $id }}-eliminados" aria-selected="false">Eliminados</a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">
            @foreach (['activos' => true, 'eliminados' => false] as $pestania => $activa)
                <div class="tab-pane fade {{ $activa ? 'show active' : '' }}" id="{{ $id }}-{{ $pestania }}" role="tabpanel" aria-labelledby="{{ $id }}-tab-{{ $pestania }}">
                    <x-datatable :id="$id.'-tabla-'.$pestania" :url="$url" :columnas="$columnas" :params="['pestania' => $pestania]" :orden="$orden ?? [[0, 'asc']]" />
                </div>
            @endforeach
        </div>
    </div>
</div>
