{{-- CU2 P3 Confirmación (CU2_inscripcion/wireframes/P3.png, Fig. 70): paso 25; "Aceptar" vuelve a P1 Gestión de Alumnos. --}}
@extends('layouts.panel')

@section('title', 'Inscripción')

@section('encabezado')
    <x-header-with-title :titulo="\App\Http\Controllers\Panel\Alumnos\InscripcionController::ENCABEZADO" />
@endsection

@section('contenido')
    <x-pantalla-confirmacion :titulo="\App\Services\Alumnos\InscripcionService::CONFIRMACION"
                             :mensaje="'El alumno '.$alumno->usuario->nombre_usuario.' '.$alumno->usuario->apellido_usuario.' queda registrado en estado '.$alumno->estado_alumno->value.'.'"
                             :url-aceptar="route('panel.alumnos.index')">
        <div class="las-legajo">
            <div class="las-legajo__titulo">NUMERO DE LEGAJO</div>
            <div class="las-legajo__numero">{{ $alumno->nro_legajo }}</div>
        </div>
        <hr>
        <div class="text-left mb-4">
            <h3 class="las-subtitulo">DETALLE DE LA INSCRIPCION</h3>
            <div class="las-confirmacion__lineas pl-2">
                <div>Carrera: {{ $alumno->carrera->nombre_carrera }}</div>
                <div>Certificado de Titulo: Adjuntado · Fotocopia de DNI: Adjuntada</div>
                <div>Estado de Cuotas: Al dia</div>
            </div>
        </div>
    </x-pantalla-confirmacion>
@endsection
