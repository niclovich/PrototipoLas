{{-- CU3 P3 Formulario de inscripción a Comisiones (CU3 P3.png, Fig. 74): pasos 9–17; alt. 12.1 y 15.1 en modal. --}}
@extends('layouts.panel')

@section('title', 'Alumno Comisiones')

@section('encabezado')
    <x-header-with-title :titulo="\App\Http\Controllers\Panel\Alumnos\InscripcionComisionController::ENCABEZADO" />
@endsection

@section('contenido')
    <h2 class="h4 font-weight-bold">Registrar Inscripcion a Comision</h2>
    <hr>
    @include('panel.alumnos._datos-alumno')
    @include('panel.alumnos.comisiones._seleccionar', ['titulo' => '1 · Seleccionar Comision', 'action' => route('panel.alumno-comisiones.create', $alumno)])
    @include('panel.alumnos.comisiones._materias', ['titulo' => 'MATERIAS DE LA COMISION SELECCIONADA'])

    <form action="{{ route('panel.alumno-comisiones.store', $alumno) }}" method="POST" class="d-flex flex-wrap justify-content-end mt-4">
        @csrf
        <input type="hidden" name="comision_id" value="{{ $seleccionada?->id }}">
        <a href="{{ route('panel.alumno-comisiones.index', $alumno) }}" class="btn btn-outline-las las-btn-principal mb-2 mr-3">Cancelar</a>
        <button type="submit" class="btn btn-las las-btn-principal mb-2" @disabled(! $materias || $aviso)>Registrar Inscripcion</button>
    </form>

    <x-modal-aviso id="modal-aviso-comision" titulo="Inscripción en Comisiones" :mensaje="$aviso" :abrir="(bool) $aviso" foco="#comision_id" />
@endsection
