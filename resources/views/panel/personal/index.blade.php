{{-- P1 Gestión de Personal (wireframes P1.png y P1_Gestion_de_Personal_pestana_Eliminados.png). --}}
@extends('layouts.panel')

@section('title', 'Gestión de Personal')

@section('encabezado')
    <x-header-with-title titulo="GESTIÓN DE PERSONAL" />
@endsection

@section('contenido')
    @php
        $filtros = ['#filtro-buscar', '#filtro-rol'];
        $comunes = [
            ['data' => 'dni_usuario', 'titulo' => 'DNI', 'orderable' => false, 'prioridad' => 3],
            ['data' => 'apellido_nombre', 'titulo' => 'APELLIDO Y NOMBRE', 'orderable' => false, 'prioridad' => 1],
            ['data' => 'rol', 'titulo' => 'ROL', 'orderable' => false, 'prioridad' => 4],
        ];
        $cierre = [
            ['data' => 'estado', 'titulo' => 'ESTADO', 'orderable' => false, 'searchable' => false, 'prioridad' => 5],
            ['data' => 'acciones', 'titulo' => 'ACCIONES', 'orderable' => false, 'searchable' => false, 'clase' => 'text-nowrap', 'prioridad' => 2],
        ];
    @endphp

    <div class="card card-las-plano">
        <div class="card-body">
            <div class="las-pestanias d-flex flex-wrap align-items-end justify-content-between">
                <ul class="nav nav-tabs las-tabs border-0" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="personal-tab-activos" data-toggle="tab" href="#personal-activos" role="tab"
                           aria-controls="personal-activos" aria-selected="true">Activos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="personal-tab-eliminados" data-toggle="tab" href="#personal-eliminados" role="tab"
                           aria-controls="personal-eliminados" aria-selected="false">Eliminados</a>
                    </li>
                </ul>
                @can('crear_personal')
                    <a href="{{ route('panel.personal.create') }}" class="btn btn-las las-btn-principal mb-2">Registrar Personal</a>
                @endcan
            </div>

            <div class="las-filtros mt-3">
                <label for="filtro-buscar" class="las-etiqueta-filtro">Filtrar</label>
                <div class="row">
                    <div class="col-12 col-md-5 mb-2">
                        <input type="search" id="filtro-buscar" name="buscar" class="form-control" placeholder="Buscar por DNI o nombre.."
                               aria-label="Buscar por DNI o nombre" autocomplete="off">
                    </div>
                    <div class="col-12 col-md-4 mb-2">
                        <select id="filtro-rol" name="rol" class="form-control las-select-bordo" aria-label="Filtrar por rol">
                            <option value="">Rol: Todos</option>
                            @foreach ($roles as $rol)
                                <option value="{{ $rol->name }}">{{ $rol->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="tab-content mt-2">
                <div class="tab-pane fade show active" id="personal-activos" role="tabpanel" aria-labelledby="personal-tab-activos">
                    <x-datatable id="personal-tabla-activos" :url="route('panel.personal.datatable')" :params="['pestania' => 'activos']"
                                 :filtros="$filtros" dom="rtip" :orden="[]" :columnas="[
                                     ...$comunes,
                                     ['data' => 'email', 'titulo' => 'CORREO', 'orderable' => false, 'prioridad' => 6],
                                     ['data' => 'telefono_usuario', 'titulo' => 'TELÉFONO', 'orderable' => false, 'prioridad' => 7],
                                     ...$cierre,
                                 ]" />
                </div>
                <div class="tab-pane fade" id="personal-eliminados" role="tabpanel" aria-labelledby="personal-tab-eliminados">
                    <x-datatable id="personal-tabla-eliminados" :url="route('panel.personal.datatable')" :params="['pestania' => 'eliminados']"
                                 :filtros="$filtros" dom="rtip" :orden="[]" :columnas="[
                                     ...$comunes,
                                     ['data' => 'fecha_alta_texto', 'titulo' => 'FECHA ALTA', 'orderable' => false, 'searchable' => false, 'prioridad' => 6],
                                     ['data' => 'fecha_baja', 'titulo' => 'FECHA BAJA', 'orderable' => false, 'searchable' => false, 'prioridad' => 7],
                                     ['data' => 'motivo_baja', 'titulo' => 'MOTIVO DE BAJA', 'orderable' => false, 'searchable' => false, 'prioridad' => 8],
                                     ...$cierre,
                                 ]" />
                </div>
            </div>
        </div>
    </div>

    @can('eliminar_personal')
        @include('panel.personal._modal-baja')
    @endcan
@endsection
