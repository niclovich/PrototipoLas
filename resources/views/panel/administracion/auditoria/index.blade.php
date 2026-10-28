{{-- Módulo Auditoría (base común, sin wireframe): listado de activity_log con filtros básicos y detalle de cambios. --}}
@extends('layouts.panel')

@section('title', 'Auditoría')

@section('encabezado')
    <x-header-with-title titulo="Auditoría" />
@endsection

@section('contenido')
    <div class="card card-las-plano">
        <div class="card-body">
            <div class="row align-items-end las-fila-filtro">
                <div class="col-12 col-md-4 mb-3">
                    <label for="filtro-buscar" class="las-etiqueta-filtro">Filtrar</label>
                    <input type="search" id="filtro-buscar" name="buscar" class="form-control las-buscador" placeholder="Buscar.."
                           aria-label="Buscar por usuario o módulo" autocomplete="off">
                </div>
                <div class="col-6 col-md-2 mb-3">
                    <label for="filtro-modulo" class="las-etiqueta-filtro">Módulo</label>
                    <select id="filtro-modulo" name="modulo" class="form-control">
                        <option value="">Todos</option>
                        @foreach ($modulos as $modulo)
                            <option value="{{ $modulo }}">{{ $modulo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2 mb-3">
                    <label for="filtro-evento" class="las-etiqueta-filtro">Evento</label>
                    <select id="filtro-evento" name="evento" class="form-control">
                        <option value="">Todos</option>
                        @foreach (\App\Support\EventoAuditoria::EVENTOS as $valor => $etiqueta)
                            <option value="{{ $valor }}">{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <x-form.date name="desde" label="Desde" />
                </div>
                <div class="col-6 col-md-2">
                    <x-form.date name="hasta" label="Hasta" />
                </div>
            </div>

            <x-datatable id="auditoria-tabla" :url="route('panel.auditoria.datatable')" dom="rtip" :orden="[]"
                         :filtros="['#filtro-buscar', '#filtro-modulo', '#filtro-evento', '#desde', '#hasta']"
                         :columnas="[
                             ['data' => 'fecha', 'titulo' => 'Fecha', 'orderable' => false, 'searchable' => false, 'prioridad' => 1],
                             ['data' => 'usuario', 'titulo' => 'Usuario', 'orderable' => false, 'searchable' => false, 'prioridad' => 3],
                             ['data' => 'modulo', 'titulo' => 'Módulo', 'orderable' => false, 'searchable' => false, 'prioridad' => 4],
                             ['data' => 'evento', 'titulo' => 'Evento', 'orderable' => false, 'searchable' => false, 'prioridad' => 5],
                             ['data' => 'registro', 'titulo' => 'Registro', 'orderable' => false, 'searchable' => false, 'prioridad' => 6],
                             ['data' => 'acciones', 'titulo' => 'Acción', 'orderable' => false, 'searchable' => false, 'clase' => 'text-nowrap', 'prioridad' => 2],
                         ]" />
        </div>
    </div>
@endsection
