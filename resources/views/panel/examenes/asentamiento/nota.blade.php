{{-- CU6 P3 Formulario Registrar Nota del Alumno (CU6 P3.png, Fig. 85): pasos 7–13; la Condición Final la calcula el sistema (nota ≥ 4). --}}
@extends('layouts.panel')

@section('title', 'Asentamiento de Acta')

@section('encabezado')
    <x-header-with-title :titulo="\App\Http\Controllers\Panel\Examenes\AsentamientoActaController::ENCABEZADO" />
@endsection

@php
    $acta = $detalle->acta;
    $notaActual = $detalle->nota_acta === null ? null : \App\Services\Alumnos\RegularidadService::formatearNota($detalle->nota_acta);
@endphp

@section('contenido')
    <form action="{{ route('panel.asentamiento.update', $detalle) }}" method="POST" novalidate>
        @csrf
        @method('PUT')
        <div class="card las-tarjeta mb-4">
            <div class="card-body">
                <h3 class="las-subtitulo las-subtitulo--tarjeta">Datos del Alumno y el Acta</h3>
                <div class="row">
                    <div class="col-12 col-md-2">
                        <x-form.input name="legajo" label="Legajo" :value="$detalle->alumno->nro_legajo" readonly />
                    </div>
                    <div class="col-12 col-md-4">
                        <x-form.input name="alumno" label="Alumno" :value="$detalle->alumno->usuario->nombre_completo" readonly />
                    </div>
                    <div class="col-12 col-md-2">
                        <x-form.input name="nro_acta" label="Nro Acta" :value="$acta->nro_acta_texto" readonly />
                    </div>
                    <div class="col-12 col-md-4">
                        <x-form.input name="materia" label="Materia" :value="$acta->detallePlan->materia->nombre_materia" readonly />
                    </div>
                </div>
            </div>
        </div>

        <div class="card las-tarjeta mb-4">
            <div class="card-body">
                <h3 class="las-subtitulo las-subtitulo--tarjeta">1 · Nota, Observacion y Condicion</h3>
                <div class="row">
                    <div class="col-12 col-md-3">
                        <x-form.input name="nota_acta" label="Nota" :value="$notaActual" inputmode="decimal" requerido
                                      data-nota-examen data-nota-aprobacion="{{ \App\Services\Examenes\AsentamientoActaService::NOTA_APROBACION }}" />
                    </div>
                    <div class="col-12 col-md-5">
                        <x-form.input name="condicion_final" label="Condicion Final" :value="$detalle->condicion_final?->value" readonly data-condicion-final />
                    </div>
                </div>
                <x-form.textarea name="observacion" label="Observacion" :value="$detalle->observacion" />
            </div>
        </div>

        <div class="d-flex flex-wrap justify-content-end mt-4">
            <a href="{{ route('panel.asentamiento.show', $acta) }}" class="btn btn-outline-las las-btn-principal mb-2 mr-3">Cancelar</a>
            <button type="submit" class="btn btn-las las-btn-principal mb-2">Guardar</button>
        </div>
    </form>

    {{-- Alt. 10.1: "La nota ingresada no está dentro del rango permitido" → Aceptar → vuelve al paso 8. --}}
    <x-modal-aviso-errores :campos="['nota_acta' => \App\Http\Controllers\Panel\Examenes\AsentamientoActaController::TITULO]" />
@endsection
