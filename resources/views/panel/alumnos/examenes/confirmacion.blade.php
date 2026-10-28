{{-- CU5 P3 Confirmación (estados Fig. 80; sin PNG: patrón compartido): pasos 16–17, "Descargar Comprobante" y "Aceptar" (→ P1). --}}
@extends('layouts.panel')

@section('title', 'Inscripción a Exámenes')

@section('encabezado')
    <x-header-with-title :titulo="\App\Http\Controllers\Panel\Alumnos\InscripcionExamenController::ENCABEZADO" />
@endsection

@section('contenido')
    <x-pantalla-confirmacion :titulo="\App\Services\Examenes\InscripcionExamenService::CONFIRMACION"
                             :mensaje="'El alumno '.$detalle->alumno->usuario->nombre_completo.' queda inscripto en el acta de examen seleccionada.'"
                             titulo-lineas="Detalle de la Inscripcion" :lineas="$lineas" :url-aceptar="route('panel.alumnos.index')">
        @can('exportar_inscripcion_examen')
            <x-slot name="acciones">
                <a href="{{ route('panel.inscripciones-examen.comprobante', $detalle) }}" class="btn btn-outline-las las-btn-principal px-5">
                    <i class="fas fa-download mr-1" aria-hidden="true"></i> Descargar Comprobante
                </a>
            </x-slot>
        @endcan
    </x-pantalla-confirmacion>
@endsection
