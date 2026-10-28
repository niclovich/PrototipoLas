{{-- P1 Gestión de Materias (wireframe P1.png): entrada de CU 1 y CU 2. --}}
@extends('layouts.panel')

@section('title', 'Gestión de Materias')

@section('encabezado')
    <x-header-with-title titulo="GESTIÓN DE MATERIAS" />
@endsection

@section('contenido')
    <div class="card card-las-plano">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between las-fila-filtro">
                <div class="las-filtros flex-grow-1 mt-4 mb-3 order-2 order-md-1">
                    <label for="filtro-buscar" class="las-etiqueta-filtro">Filtrar</label>
                    <input type="search" id="filtro-buscar" name="buscar" class="form-control las-buscador" placeholder="Buscar por nombre.."
                           aria-label="Buscar por nombre" autocomplete="off">
                </div>
                @can('crear_materia')
                    <div class="order-1 order-md-2 mb-2">
                        <a href="{{ route('panel.materias.create') }}" class="btn btn-las las-btn-principal">+ Registrar Materia</a>
                    </div>
                @endcan
            </div>

            {{-- "Mostrar 10 registros" está en el wireframe: dom "lrtip" (D9 de E5). --}}
            <x-datatable id="materias-tabla" :url="route('panel.materias.datatable')" :filtros="['#filtro-buscar']" dom="lrtip" :orden="[]"
                         :columnas="[
                             ['data' => 'nombre_materia', 'titulo' => 'NOMBRE DE LA MATERIA', 'orderable' => false, 'prioridad' => 1],
                             ['data' => 'estado', 'titulo' => 'ESTADO', 'orderable' => false, 'searchable' => false, 'prioridad' => 3],
                             ['data' => 'acciones', 'titulo' => 'ACCIONES', 'orderable' => false, 'searchable' => false, 'clase' => 'text-nowrap', 'prioridad' => 2],
                         ]" />
        </div>
    </div>

    {{-- CU 2 alt. 4: materia vinculada a un Plan de Estudio (fin del CU, sobre P1). --}}
    <x-modal-aviso id="modal-materia-vinculada" titulo="Materia vinculada a un Plan de Estudio"
                   :mensaje="session(\App\Http\Controllers\Panel\Academico\MateriaController::AVISO)"
                   :abrir="session()->has(\App\Http\Controllers\Panel\Academico\MateriaController::AVISO)" />
@endsection
