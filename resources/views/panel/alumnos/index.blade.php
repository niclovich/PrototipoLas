{{-- P1 Gestión de Alumnos (Fig. 72; pestaña Preinscriptos = Fig. 68): entrada de CU2, CU3 (y Cambio), CU5 y CU7. --}}
@extends('layouts.panel')

@section('title', 'Gestión de Alumnos')

@section('encabezado')
    <x-header-with-title titulo="GESTIÓN DE ALUMNOS" />
@endsection

@section('contenido')
    @php
        $columnas = [
            ['data' => 'control', 'titulo' => '−', 'orderable' => false, 'searchable' => false, 'clase' => 'las-col-control', 'prioridad' => 1],
            ['data' => 'nombre', 'titulo' => 'NOMBRE', 'orderable' => false, 'prioridad' => 1],
            ['data' => 'dni_usuario', 'titulo' => 'DNI', 'orderable' => false, 'prioridad' => 2],
            ['data' => 'email', 'titulo' => 'EMAIL', 'orderable' => false, 'prioridad' => 3],
        ];
        $pestanias = ['activos' => 'Activos', \App\DataTables\AlumnoDataTable::PESTANIA_PREINSCRIPTOS => 'Preinscriptos'];
    @endphp

    <div class="card card-las-plano">
        <div class="card-body">
            <div class="las-pestanias d-flex flex-wrap align-items-end justify-content-between">
                <ul class="nav nav-tabs las-tabs border-0" role="tablist">
                    @foreach ($pestanias as $clave => $texto)
                        <li class="nav-item">
                            <a @class(['nav-link', 'active' => $loop->first]) id="alumnos-tab-{{ $clave }}" data-toggle="tab" href="#alumnos-{{ $clave }}" role="tab"
                               aria-controls="alumnos-{{ $clave }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $texto }}</a>
                        </li>
                    @endforeach
                </ul>
                {{-- E14-D2: "Registrar Alumno" inicia CU1 Preinscripción. --}}
                @can('crear_preinscripcion')
                    <a href="{{ route('panel.preinscripcion.create') }}" class="btn btn-las las-btn-principal mb-2">Registrar Alumno</a>
                @else
                    <span class="d-inline-block mb-2" tabindex="0" data-toggle="tooltip" title="No tiene permiso para registrar alumnos">
                        <button type="button" class="btn btn-las las-btn-principal las-btn-deshabilitado" disabled>Registrar Alumno</button>
                    </span>
                @endcan
            </div>

            <div class="mt-3">
                <label for="filtro-buscar" class="las-etiqueta-filtro">Filtrar</label>
                <div class="row">
                    <div class="col-12 col-md-5 mb-2">
                        <input type="search" id="filtro-buscar" name="buscar" class="form-control" placeholder="Buscar.." aria-label="Buscar por DNI o nombre" autocomplete="off">
                    </div>
                </div>
            </div>

            <div class="tab-content mt-2">
                @foreach ($pestanias as $clave => $texto)
                    <div @class(['tab-pane fade', 'show active' => $loop->first]) id="alumnos-{{ $clave }}" role="tabpanel" aria-labelledby="alumnos-tab-{{ $clave }}">
                        <x-datatable :id="'alumnos-tabla-'.$clave" :url="route('panel.alumnos.datatable')" :params="['pestania' => $clave]"
                                     :filtros="['#filtro-buscar']" dom="lrtip" :orden="[]" :columnas="$columnas" data-detalle-expandible />
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Avisos que terminan el CU en P1: CU7 alt. 2.2 y 6.2, CU2 alt. 19.1, CU3 alt. 5.1/5.2. --}}
    <x-modal-aviso id="modal-aviso-alumnos" :titulo="session(\App\Http\Controllers\Panel\Alumnos\AlumnoController::AVISO_TITULO, 'Registrar Pago de Cuotas')"
                   :lista="session(\App\Http\Controllers\Panel\Alumnos\AlumnoController::AVISO_LISTA, [])"
                   :mensaje="session(\App\Http\Controllers\Panel\Alumnos\AlumnoController::AVISO)"
                   :abrir="session()->has(\App\Http\Controllers\Panel\Alumnos\AlumnoController::AVISO)" />
@endsection
