{{-- P1 Gestión de Carreras (wireframe Registrar_P1.png): entrada de CU 3 y CU 4. --}}
@extends('layouts.panel')

@section('title', 'Gestión de Carreras')

@section('encabezado')
    <x-header-with-title titulo="GESTIÓN DE CARRERAS" />
@endsection

@section('contenido')
    <div class="card card-las-plano">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-end justify-content-between las-fila-filtro">
                <div class="las-filtros flex-grow-1 mb-3">
                    <label for="filtro-buscar" class="las-etiqueta-filtro">Filtrar</label>
                    <input type="search" id="filtro-buscar" name="buscar" class="form-control las-buscador" placeholder="Buscar.."
                           aria-label="Buscar por nombre, resolución, modalidad o estado" autocomplete="off">
                </div>
                @can('crear_carrera')
                    <a href="{{ route('panel.carreras.create') }}" class="btn btn-las las-btn-principal mb-3">Registrar Carrera</a>
                @endcan
            </div>

            <x-datatable id="carreras-tabla" :url="route('panel.carreras.datatable')" :filtros="['#filtro-buscar']" dom="rtip" :orden="[]"
                         :columnas="[
                             ['data' => 'nombre_carrera', 'titulo' => 'CARRERA', 'orderable' => false, 'prioridad' => 1],
                             ['data' => 'modalidad', 'titulo' => 'MODALIDAD', 'orderable' => false, 'searchable' => false, 'prioridad' => 4],
                             ['data' => 'duracion', 'titulo' => 'DURACIÓN', 'orderable' => false, 'searchable' => false, 'prioridad' => 5],
                             ['data' => 'resolucion', 'titulo' => 'RESOLUCIÓN', 'orderable' => false, 'searchable' => false, 'prioridad' => 6],
                             ['data' => 'estado', 'titulo' => 'ESTADO', 'orderable' => false, 'searchable' => false, 'prioridad' => 3],
                             ['data' => 'acciones', 'titulo' => 'ACCIÓN', 'orderable' => false, 'searchable' => false, 'clase' => 'text-nowrap', 'prioridad' => 2],
                         ]" />
        </div>
    </div>
@endsection
