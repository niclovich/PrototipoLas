{{--
    DataTable server-side (POST con CSRF), en español y responsive. Lo inicializa resources/js/datatables-es.js.
    columnas: [['data' => 'campo', 'titulo' => 'Título', 'orderable' => true, 'searchable' => true, 'clase' => '', 'prioridad' => 1], ...]
    params: datos extra en cada pedido (p. ej. ['pestania' => 'activos']).
--}}
@props(['id', 'url', 'columnas', 'params' => [], 'orden' => [[0, 'asc']]])
@php
    $config = collect($columnas)->map(fn ($c) => array_filter([
        'data' => $c['data'],
        'name' => $c['name'] ?? $c['data'],
        'orderable' => $c['orderable'] ?? true,
        'searchable' => $c['searchable'] ?? true,
        'className' => $c['clase'] ?? null,
        'responsivePriority' => $c['prioridad'] ?? null,
    ], fn ($valor) => $valor !== null))->values();
@endphp
<table id="{{ $id }}" {{ $attributes->merge(['class' => 'table table-bordered table-striped table-hover dt-responsive w-100']) }}
       data-datatable
       data-url="{{ $url }}"
       data-columnas="{{ $config->toJson() }}"
       data-params="{{ json_encode((object) $params) }}"
       data-orden="{{ json_encode($orden) }}">
    <thead class="las-thead">
        <tr>
            @foreach ($columnas as $columna)
                <th scope="col">{{ $columna['titulo'] }}</th>
            @endforeach
        </tr>
    </thead>
</table>
