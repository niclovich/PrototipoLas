{{-- P1 Gestión de Actas Examen (wireframe P1.png, Fig. 233): entrada de CU 21 y CU 22. --}}
@extends('layouts.panel')

@section('title', 'Gestión de Actas Examen')

@section('encabezado')
    <x-header-with-title titulo="GESTIÓN DE ACTAS EXAMEN" />
@endsection

@section('contenido')
    <div class="card card-las-plano">
        <div class="card-body">
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-6 text-center form-group">
                    <label for="filtro-carrera" class="text-muted">Seleccionar Carrera</label>
                    <select id="filtro-carrera" name="carrera" class="form-control las-select-bordo" data-recargar-carrera
                            data-url="{{ route('panel.actas.index') }}">
                        @foreach ($carreras as $id => $nombre)
                            <option value="{{ $id }}" @selected($carrera?->id === $id)>{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if ($carrera)
                <div class="row align-items-end las-fila-filtro">
                    <div class="col-12 col-md-5 col-lg-4 mb-3">
                        <label for="filtro-buscar" class="las-etiqueta-filtro">Filtrar</label>
                        <input type="search" id="filtro-buscar" name="buscar" class="form-control las-buscador" placeholder="Buscar.."
                               aria-label="Buscar por número de acta, materia, llamado o tipo" autocomplete="off">
                    </div>
                    <div class="col-12 col-md-3 col-lg-2 mb-3">
                        <label for="filtro-asentada" class="las-etiqueta-filtro">Asentada</label>
                        <select id="filtro-asentada" name="asentada" class="form-control">
                            <option value="">No/Si</option>
                            <option value="N">No</option>
                            <option value="S">Si</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4 col-lg-6 text-md-right mb-3">
                        @can('crear_acta_examen')
                            <a href="{{ route('panel.actas.create', $carrera) }}" class="btn btn-las las-btn-principal">Registrar Acta</a>
                        @endcan
                    </div>
                </div>

                <x-datatable id="actas-tabla" :url="route('panel.actas.datatable')" :params="['carrera' => $carrera->id]"
                             :filtros="['#filtro-buscar', '#filtro-asentada']" dom="rtip" :orden="[]"
                             :columnas="[
                                 ['data' => 'nro', 'titulo' => 'Nro Acta', 'orderable' => false, 'searchable' => false, 'prioridad' => 1],
                                 ['data' => 'materia', 'titulo' => 'Materia', 'orderable' => false, 'searchable' => false, 'prioridad' => 3],
                                 ['data' => 'modalidad', 'titulo' => 'Modalidad', 'orderable' => false, 'searchable' => false, 'prioridad' => 8],
                                 ['data' => 'llamado', 'titulo' => 'Llamado', 'orderable' => false, 'searchable' => false, 'prioridad' => 5],
                                 ['data' => 'tipo', 'titulo' => 'Tipo Acta', 'orderable' => false, 'searchable' => false, 'prioridad' => 7],
                                 ['data' => 'fecha', 'titulo' => 'Fecha', 'orderable' => false, 'searchable' => false, 'prioridad' => 4],
                                 ['data' => 'asentada_texto', 'titulo' => 'Asentada', 'orderable' => false, 'searchable' => false, 'prioridad' => 6],
                                 ['data' => 'acciones', 'titulo' => 'Acción', 'orderable' => false, 'searchable' => false, 'clase' => 'text-nowrap', 'prioridad' => 2],
                             ]" />
            @else
                <p class="text-muted text-center mb-0">No hay carreras en estado Activo.</p>
            @endif
        </div>
    </div>

    {{-- CU 22 alt. 8.1 y avisos de CU6: Aceptar → vuelve a P1. --}}
    <x-modal-aviso id="modal-aviso-acta" :titulo="session(\App\Http\Controllers\Panel\Examenes\ActaExamenController::AVISO_TITULO, 'Acta con alumnos inscriptos')"
                   :mensaje="session(\App\Http\Controllers\Panel\Examenes\ActaExamenController::AVISO)"
                   :abrir="session()->has(\App\Http\Controllers\Panel\Examenes\ActaExamenController::AVISO)" />
@endsection
