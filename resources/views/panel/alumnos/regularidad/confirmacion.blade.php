{{-- CU4 P5 Confirmación (CU4_regularidad/wireframes/P5.png): paso 25; "Aceptar" vuelve a P3. --}}
@extends('layouts.panel')

@section('title', 'Regularidad Académica')

@section('encabezado')
    <x-header-with-title :titulo="\App\Http\Controllers\Panel\Alumnos\RegularidadController::ENCABEZADO" />
@endsection

@php
    $det = $inscripcion->detComision;
    $formato = [\App\Services\Alumnos\RegularidadService::class, 'formatearNota'];
    // Como el ejemplo del wireframe: solo las notas cargadas.
    $notas = collect(\App\Services\Alumnos\RegularidadService::NOTAS)
        ->filter(fn ($etiqueta, $campo) => $inscripcion->{$campo} !== null)
        ->map(fn ($etiqueta, $campo) => $etiqueta.': '.$formato($inscripcion->{$campo}))
        ->push('Faltas: '.($inscripcion->faltas_alumno ?? 0))
        ->implode(' · ');
    $lineas = [
        "Legajo: {$inscripcion->alumno->nro_legajo} · Alumno: {$inscripcion->alumno->usuario->nombre_completo}",
        "Materia: {$det->detallePlan->materia->nombre_materia} ({$det->detallePlan->codigo_det_plan_estudio}) · Comision: {$det->comision->codigo_comision}",
        $notas,
        'Condicion Final: '.\App\Enums\CondicionCursada::etiqueta($inscripcion->condicion_final),
    ];
@endphp

@section('contenido')
    <x-pantalla-confirmacion :titulo="\App\Services\Alumnos\RegularidadService::CONFIRMACION" :mensaje="\App\Services\Alumnos\RegularidadService::CONFIRMACION_DETALLE"
                             titulo-lineas="Detalle del Registro" :lineas="$lineas" :url-aceptar="route('panel.regularidad.alumnos', $det)" />
@endsection
