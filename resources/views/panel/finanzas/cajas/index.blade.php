{{-- P1 Gestión de Cajas (soporte/caja/wireframes/P1.png, Fig. 250): entrada de CU 35 y CU 36. --}}
@extends('layouts.panel')

@section('title', 'Gestión de Cajas')

@section('encabezado')
    <x-header-with-title :titulo="\App\Http\Controllers\Panel\Finanzas\CajaController::ENCABEZADO" />
@endsection

@section('contenido')
    <div class="card card-las-plano">
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-12 col-md-4 form-group">
                    <label for="filtro-estado" class="las-etiqueta-filtro">Estado</label>
                    <select id="filtro-estado" name="estado" class="form-control las-select-bordo">
                        <option value="">Todas</option>
                        @foreach ($estados as $valor => $texto)
                            <option value="{{ $valor }}">{{ $texto }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-8 text-md-right form-group">
                    @can('crear_caja')
                        @if ($tieneCajaAbierta)
                            <span class="d-inline-block" tabindex="0" data-toggle="tooltip" title="{{ \App\Services\Finanzas\CajaService::MENSAJE_YA_TIENE_CAJA }}">
                                <button type="button" class="btn btn-las las-btn-principal las-btn-deshabilitado" disabled>Abrir Caja</button>
                            </span>
                        @else
                            <a href="{{ route('panel.cajas.create') }}" class="btn btn-las las-btn-principal">Abrir Caja</a>
                        @endif
                    @endcan
                </div>
            </div>

            <x-datatable id="cajas-tabla" :url="route('panel.cajas.datatable')" :filtros="['#filtro-estado']" dom="rtip" :orden="[]"
                         :columnas="[
                             ['data' => 'id', 'titulo' => 'CAJA', 'orderable' => false, 'searchable' => false, 'prioridad' => 1],
                             ['data' => 'fecha_apertura_texto', 'titulo' => 'FECHA DE APERTURA', 'orderable' => false, 'searchable' => false, 'prioridad' => 3],
                             ['data' => 'fecha_cierre_texto', 'titulo' => 'FECHA DE CIERRE', 'orderable' => false, 'searchable' => false, 'prioridad' => 5],
                             ['data' => 'cajero', 'titulo' => 'CAJERO', 'orderable' => false, 'searchable' => false, 'prioridad' => 6],
                             ['data' => 'monto_inicial_texto', 'titulo' => 'MONTO INICIAL', 'orderable' => false, 'searchable' => false, 'prioridad' => 7],
                             ['data' => 'estado', 'titulo' => 'ESTADO', 'orderable' => false, 'searchable' => false, 'prioridad' => 4],
                             ['data' => 'acciones', 'titulo' => 'ACCION', 'orderable' => false, 'searchable' => false, 'clase' => 'text-nowrap', 'prioridad' => 2],
                         ]" />
        </div>
    </div>

    <x-modal-aviso id="modal-aviso-caja" titulo="Gestión de Cajas"
                   :mensaje="session(\App\Http\Controllers\Panel\Finanzas\CajaController::AVISO)"
                   :abrir="session()->has(\App\Http\Controllers\Panel\Finanzas\CajaController::AVISO)" />
@endsection
