{{-- CU 19 P3 Formulario Cambiar Comisión (CU3 Cambiar_P3.png, Fig. 231): pasos 5–12; alt. 9.1 en modal. --}}
@extends('layouts.panel')

@section('title', 'Alumno Comisiones')

@section('encabezado')
    <x-header-with-title :titulo="\App\Http\Controllers\Panel\Alumnos\InscripcionComisionController::ENCABEZADO" />
@endsection

@section('contenido')
    <h2 class="h4 font-weight-bold">Cambiar Comision</h2>
    <hr>
    @include('panel.alumnos._datos-alumno')

    <div class="card las-tarjeta mb-4">
        <div class="card-body">
            <h3 class="las-subtitulo las-subtitulo--tarjeta">Comision Actual</h3>
            <div class="row">
                <div class="col-12 col-lg-8">
                    <x-form.input name="comision_actual" :value="$origen->etiqueta(true)" readonly aria-label="Comision Actual" grupo-clase="mb-0" />
                </div>
            </div>
        </div>
    </div>

    @include('panel.alumnos.comisiones._seleccionar', ['titulo' => '1 · Seleccionar Nueva Comision', 'action' => route('panel.alumno-comisiones.cambio', $alumno)])
    @include('panel.alumnos.comisiones._materias', ['titulo' => 'MATERIAS DE LA NUEVA COMISION'])

    {{-- Paso 11: confirmación del cambio (origen, destino y materias a transferir). --}}
    <form action="{{ route('panel.alumno-comisiones.cambio.update', $alumno) }}" method="POST" class="d-flex flex-wrap justify-content-end mt-4"
          @if ($seleccionada && $materias) data-confirm="Se cambiará la comisión {{ $origen->codigo_comision }} por {{ $seleccionada->codigo_comision }} y se transferirán {{ collect($materias)->where('habilitada', true)->count() }} materias." @endif>
        @csrf
        @method('PUT')
        <input type="hidden" name="comision_id" value="{{ $seleccionada?->id }}">
        <a href="{{ route('panel.alumno-comisiones.index', $alumno) }}" class="btn btn-outline-las las-btn-principal mb-2 mr-3">Cancelar</a>
        <button type="submit" class="btn btn-las las-btn-principal mb-2" @disabled(! $materias || $aviso)>Cambiar Comision</button>
    </form>

    <x-modal-aviso id="modal-aviso-cambio" titulo="Cambio de Comisión" :mensaje="$aviso" :abrir="(bool) $aviso" foco="#comision_id" />
@endsection
