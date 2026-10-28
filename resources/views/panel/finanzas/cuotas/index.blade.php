{{-- P1 Gestión de Cuotas (wireframe P1.png, Fig. 245): entrada de CU 33 y CU 34. --}}
@extends('layouts.panel')

@section('title', 'Gestión de Cuotas')

@section('encabezado')
    <x-header-with-title titulo="GESTIÓN DE CUOTAS" />
@endsection

@section('contenido')
    <div class="card card-las-plano">
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-12 col-md-5 form-group">
                    <label for="filtro-carrera" class="las-etiqueta-filtro">Seleccionar Carrera</label>
                    <select id="filtro-carrera" name="carrera" class="form-control las-select-bordo" data-recargar-carrera
                            data-url="{{ route('panel.cuotas.index') }}">
                        @foreach ($carreras as $id => $nombre)
                            <option value="{{ $id }}" @selected($carrera?->id === $id)>{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3 form-group">
                    <label for="filtro-anio" class="las-etiqueta-filtro">Año Lectivo</label>
                    <select id="filtro-anio" name="anio" class="form-control las-select-bordo" data-recargar-carrera
                            data-url="{{ route('panel.cuotas.index') }}">
                        @foreach ($anios as $valor)
                            <option value="{{ $valor }}" @selected($anio === $valor)>{{ $valor }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4 text-md-right form-group">
                    @if ($carrera)
                        @can('crear_cuota')
                            <a href="{{ route('panel.cuotas.create', ['carrera' => $carrera, 'anio' => $anio]) }}" class="btn btn-las las-btn-principal">Registrar Cuota</a>
                        @endcan
                    @endif
                </div>
            </div>

            @if ($carrera)
                <x-datatable id="cuotas-tabla" :url="route('panel.cuotas.datatable')" :params="['carrera' => $carrera->id, 'anio' => $anio]" dom="rtip" :orden="[]"
                             :columnas="[
                                 ['data' => 'mes', 'titulo' => 'MES', 'orderable' => false, 'searchable' => false, 'prioridad' => 1],
                                 ['data' => 'anio', 'titulo' => 'AÑO', 'orderable' => false, 'searchable' => false, 'prioridad' => 4],
                                 ['data' => 'monto_texto', 'titulo' => 'MONTO', 'orderable' => false, 'searchable' => false, 'prioridad' => 3],
                                 ['data' => 'estado', 'titulo' => 'ESTADO', 'orderable' => false, 'searchable' => false, 'prioridad' => 5],
                                 ['data' => 'acciones', 'titulo' => 'ACCIÓN', 'orderable' => false, 'searchable' => false, 'clase' => 'text-nowrap', 'prioridad' => 2],
                             ]" />
            @else
                <p class="text-muted text-center mb-0">No hay carreras en estado Pendiente o Activo.</p>
            @endif
        </div>
    </div>

    {{-- CU 34 alt. 7.1 y 11.1: Aceptar → vuelve a P1. --}}
    <x-modal-aviso id="modal-aviso-cuota" titulo="Cuota no modificable"
                   :mensaje="session(\App\Http\Controllers\Panel\Finanzas\CuotaController::AVISO)"
                   :abrir="session()->has(\App\Http\Controllers\Panel\Finanzas\CuotaController::AVISO)" />
@endsection
