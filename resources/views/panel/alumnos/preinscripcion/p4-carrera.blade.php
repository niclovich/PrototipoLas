{{-- CU1 P4 Carrera y documentacion (Paso_4_Carrera_y_Documentacion.png, Fig. 65): pasos 8–19. --}}
@extends('layouts.panel')

@section('title', 'Preinscripción · Carrera')

@section('encabezado')
    <x-header-with-title :titulo="\App\Http\Controllers\Panel\Alumnos\PreinscripcionController::ENCABEZADO" />
@endsection

@php
    $documentos = [
        'documento_dni' => ['Fotocopia de DNI', 'Opcional, se puede completar mas adelante.'],
        'documento_titulo' => ['Certificado de Titulo', 'Titulo o certificado de titulo en tramite. Opcional, se puede completar mas adelante.'],
    ];
@endphp

@section('contenido')
    @include('panel.alumnos.preinscripcion._pasos', ['paso' => 4])

    <form action="{{ route('panel.preinscripcion.carrera.store') }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf
        <h2 class="h4 font-weight-bold mb-1">Carrera y documentacion</h2>
        <p class="text-muted mb-2">Selecciona la carrera y registra la situacion de estudios secundarios.</p>
        <div class="row align-items-end">
            <div class="col-12 col-md-5">
                <x-form.select name="carrera_id" label="Carrera" :opciones="$carreras" :value="$seleccion['carrera_id'] ?? null" placeholder="Seleccione una carrera" />
            </div>
            <div class="col-12 col-md-4">
                <x-form.select name="situacion_estudios" label="Situacion de estudios secundarios" :opciones="$situaciones"
                               :value="$seleccion['situacion_estudios'] ?? \App\Enums\SituacionEstudios::Completo->value" />
            </div>
            <div class="col-12 col-md-3">
                <div class="form-group las-casilla-campo">
                    <x-form.checkbox name="adeuda_materia" label="Adeuda materias" :checked="(bool) ($seleccion['adeuda_materia'] ?? false)" grupo-clase="mb-0" />
                </div>
            </div>
        </div>

        <h3 class="las-subtitulo mt-4">DOCUMENTACION</h3>
        <p>Se solicita fotocopia de DNI y Certificado de Titulo (o certificado de titulo en tramite).</p>

        @foreach ($documentos as $campo => [$titulo, $ayuda])
            <div class="card las-tarjeta mb-3" data-adjunto>
                <div class="card-body d-flex flex-wrap align-items-center">
                    <i class="far fa-file-alt fa-2x text-muted mr-3" aria-hidden="true"></i>
                    <div class="flex-grow-1 mr-3">
                        <div class="font-weight-bold">{{ $titulo }}</div>
                        <div class="las-ayuda">{{ $ayuda }}</div>
                        <div class="small text-success mt-1 @unless (isset($adjuntos[$campo])) d-none @endunless" data-adjunto-nombre>
                            @isset($adjuntos[$campo]) Archivo adjuntado. @endisset
                        </div>
                        @error($campo)
                            <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                        @enderror
                    </div>
                    <label for="{{ $campo }}" class="btn btn-outline-las mb-0 mt-2 mt-md-0" tabindex="0">
                        <i class="far fa-file-alt mr-1" aria-hidden="true"></i> Adjuntar
                    </label>
                    <input type="file" name="{{ $campo }}" id="{{ $campo }}" class="las-input-oculto" accept="application/pdf" data-adjunto-input>
                </div>
            </div>
        @endforeach

        {{-- E12-D1 (decisión de Nicolás): pasos 20–21 de la spec. --}}
        <x-form.textarea name="observaciones" label="Observaciones" :value="$seleccion['observaciones'] ?? null" placeholder="Opcional" />

        @include('panel.alumnos.preinscripcion._botones', ['urlAtras' => $urlAtras])
    </form>
@endsection
