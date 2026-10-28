{{-- CU6 P4 Confirmación (CU6 P4.png): paso 25, "Detalle del Asentamiento" y "Aceptar" (→ P1 Gestión de Actas Examen). --}}
@extends('layouts.panel')

@section('title', 'Asentamiento de Acta')

@section('encabezado')
    <x-header-with-title :titulo="\App\Http\Controllers\Panel\Examenes\AsentamientoActaController::ENCABEZADO" />
@endsection

@section('contenido')
    <x-pantalla-confirmacion :titulo="\App\Services\Examenes\AsentamientoActaService::CONFIRMACION"
                             :mensaje="\App\Services\Examenes\AsentamientoActaService::CONFIRMACION_DETALLE"
                             titulo-lineas="Detalle del Asentamiento" :lineas="$lineas"
                             :url-aceptar="route('panel.actas.index', ['carrera' => $acta->detallePlan->planEstudio->carrera_id])" />
@endsection
