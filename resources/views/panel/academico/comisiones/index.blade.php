{{-- P1 Gestión de Comisiones (wireframe P1.png, Fig. 227): entrada de CU 17. --}}
@extends('layouts.panel')

@section('title', 'Gestión de Comisiones')

@section('encabezado')
    <x-header-with-title titulo="GESTIÓN DE COMISIONES" />
@endsection

@section('contenido')
    <div class="card card-las-plano">
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-12 col-md-8 col-lg-6 offset-lg-3 text-center form-group">
                    <label for="filtro-carrera" class="text-muted">Seleccionar Carrera</label>
                    <select id="filtro-carrera" name="carrera" class="form-control las-select-bordo" data-recargar-carrera
                            data-url="{{ route('panel.comisiones.index') }}">
                        @foreach ($carreras as $id => $nombre)
                            <option value="{{ $id }}" @selected($carrera?->id === $id)>{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4 col-lg-3 text-md-right form-group">
                    @if ($carrera)
                        @can('crear_comision')
                            <a href="{{ route('panel.comisiones.create', $carrera) }}" class="btn btn-las las-btn-principal">Registrar Comisión</a>
                        @endcan
                    @endif
                </div>
            </div>

            @if ($carrera)
                <x-datatable id="comisiones-tabla" :url="route('panel.comisiones.datatable')" :params="['carrera' => $carrera->id]" dom="rtip" :orden="[]"
                             :columnas="[
                                 ['data' => 'codigo_comision', 'titulo' => 'CÓDIGO', 'orderable' => false, 'searchable' => false, 'prioridad' => 1],
                                 ['data' => 'anio_curso', 'titulo' => 'AÑO CURSO', 'orderable' => false, 'searchable' => false, 'prioridad' => 3],
                                 ['data' => 'anio_lectivo', 'titulo' => 'AÑO LECTIVO', 'orderable' => false, 'searchable' => false, 'prioridad' => 4],
                                 ['data' => 'horario_comision', 'titulo' => 'HORARIO', 'orderable' => false, 'searchable' => false, 'prioridad' => 6],
                                 ['data' => 'capacidad_comision', 'titulo' => 'CAPACIDAD', 'orderable' => false, 'searchable' => false, 'prioridad' => 7],
                                 ['data' => 'modalidad', 'titulo' => 'MODALIDAD', 'orderable' => false, 'searchable' => false, 'prioridad' => 8],
                                 ['data' => 'estado', 'titulo' => 'ESTADO', 'orderable' => false, 'searchable' => false, 'prioridad' => 5],
                                 ['data' => 'acciones', 'titulo' => 'ACCIONES', 'orderable' => false, 'searchable' => false, 'clase' => 'text-nowrap', 'prioridad' => 2],
                             ]" />
            @else
                <p class="text-muted text-center mb-0">No hay carreras en estado Activo.</p>
            @endif
        </div>
    </div>
@endsection
