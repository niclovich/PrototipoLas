{{-- CU3 P4 Confirmación (CU3 P4.png) y Confirmación del Cambio de Comisión (estados Fig. 229, sin PNG propio): "Aceptar" → P1. --}}
@extends('layouts.panel')

@section('title', 'Inscripción en Comisiones')

@section('encabezado')
    <x-header-with-title :titulo="\App\Http\Controllers\Panel\Alumnos\InscripcionComisionController::ENCABEZADO_CONFIRMACION" />
@endsection

@php
    $nombre = $alumno->usuario->nombre_usuario.' '.$alumno->usuario->apellido_usuario;
    $lineas = [
        "Legajo: {$alumno->nro_legajo} · Alumno: {$alumno->usuario->nombre_completo}",
        "Carrera: {$alumno->carrera->nombre_carrera}",
    ];
    if ($cambio && $origen) {
        $lineas[] = "Comision anterior: {$origen->codigo_comision} ({$origen->anioCursoTexto()}, {$origen->horario_comision})";
    }
    $lineas[] = ($cambio ? 'Comision nueva: ' : 'Comision: ')."{$comision->codigo_comision} ({$comision->anioCursoTexto()}, {$comision->horario_comision})";
    $lineas[] = ($cambio ? 'Materias transferidas: ' : 'Materias asignadas: ').$cantidad;
@endphp

@section('contenido')
    <x-pantalla-confirmacion
        :titulo="$cambio ? \App\Services\Alumnos\InscripcionComisionService::CONFIRMACION_CAMBIO : \App\Services\Alumnos\InscripcionComisionService::CONFIRMACION"
        :mensaje="'El alumno '.$nombre.($cambio ? ' queda inscripto en la nueva comision.' : ' queda inscripto en la comision seleccionada.')"
        titulo-lineas="Detalle de la Inscripcion" :lineas="$lineas" :url-aceptar="route('panel.alumnos.index')" />
@endsection
