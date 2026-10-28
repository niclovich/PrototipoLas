{{-- CU4 P4 Formulario Registrar Regularidad (CU4_regularidad/wireframes/P4.png, Fig. 79): pasos 14–21; alt. 17.1 en modal. --}}
@extends('layouts.panel')

@section('title', 'Regularidad Académica')

@section('encabezado')
    <x-header-with-title :titulo="\App\Http\Controllers\Panel\Alumnos\RegularidadController::ENCABEZADO" />
@endsection

@php
    $det = $inscripcion->detComision;
    $nota = fn (string $campo) => $inscripcion->{$campo} === null ? null : \App\Services\Alumnos\RegularidadService::formatearNota($inscripcion->{$campo});
    $etiquetas = ['nota_p1' => 'Nota Parcial 1', 'nota_p2' => 'Nota Parcial 2', 'nota_r1' => 'Nota Recuperatorio 1', 'nota_r2' => 'Nota Recuperatorio 2'];
@endphp

@section('contenido')
    <form action="{{ route('panel.regularidad.update', $inscripcion) }}" method="POST" novalidate>
        @csrf
        @method('PUT')
        <div class="card las-tarjeta mb-4">
            <div class="card-body">
                <h3 class="las-subtitulo las-subtitulo--tarjeta">Datos del Alumno y la Materia</h3>
                <div class="row">
                    <div class="col-12 col-md-3">
                        <x-form.input name="legajo" label="Legajo" :value="$inscripcion->alumno->nro_legajo" readonly />
                    </div>
                    <div class="col-12 col-md-4">
                        <x-form.input name="alumno" label="Alumno" :value="$inscripcion->alumno->usuario->nombre_completo" readonly />
                    </div>
                    <div class="col-12 col-md-5">
                        <x-form.input name="materia" label="Materia"
                                      :value="$det->detallePlan->materia->nombre_materia.' ('.$det->detallePlan->codigo_det_plan_estudio.') · '.$det->comision->codigo_comision" readonly />
                    </div>
                </div>
            </div>
        </div>

        <div class="card las-tarjeta mb-4">
            <div class="card-body">
                <h3 class="las-subtitulo las-subtitulo--tarjeta">1 · Notas, Faltas y Condicion Final</h3>
                <div class="row">
                    @foreach ($etiquetas as $campo => $etiqueta)
                        <div class="col-6 col-md">
                            <x-form.input :name="$campo" :label="$etiqueta" :value="$nota($campo)" inputmode="decimal" />
                        </div>
                    @endforeach
                    <div class="col-6 col-md">
                        <x-form.input name="faltas_alumno" label="Faltas" :value="$inscripcion->faltas_alumno" inputmode="numeric" />
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-5">
                        <x-form.select name="condicion_final" label="Condicion Final" :opciones="$condiciones" :value="$inscripcion->condicion_final?->value" />
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap justify-content-end mt-4">
            <a href="{{ route('panel.regularidad.alumnos', $det) }}" class="btn btn-outline-las las-btn-principal mb-2 mr-3">Cancelar</a>
            <button type="submit" class="btn btn-las las-btn-principal mb-2">Registrar Regularidad</button>
        </div>
    </form>

    {{-- Alt. 17.1: "La nota de evaluación no cumple con el formato." → Aceptar → vuelve al paso 14 (la nota inválida). --}}
    <x-modal-aviso-errores :campos="array_fill_keys(array_keys($etiquetas), 'Registrar Regularidad')"
                           :mensajes="array_fill_keys(array_keys($etiquetas), \App\Services\Alumnos\RegularidadService::MENSAJE_FORMATO_NOTA)" />
    <x-modal-aviso id="modal-aviso-regularidad" titulo="Registrar Regularidad"
                   :mensaje="session(\App\Http\Controllers\Panel\Alumnos\RegularidadController::AVISO)"
                   :abrir="session()->has(\App\Http\Controllers\Panel\Alumnos\RegularidadController::AVISO)" />
@endsection
