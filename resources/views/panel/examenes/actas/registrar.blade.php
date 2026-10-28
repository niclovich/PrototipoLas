{{-- CU 21: P2 Formulario Registrar Acta Examen (P2.png, Fig. 234). La carrera viene de P1. --}}
@extends('layouts.panel')

@section('title', 'Registrar Acta de Examen')

@section('encabezado')
    <x-header-with-title titulo="GESTIÓN DE ACTAS EXAMEN" />
@endsection

@section('contenido')
    @include('panel.examenes.actas._form', [
        'modo' => 'registrar',
        'accion' => route('panel.actas.store', $carrera),
        'metodo' => 'POST',
        'titulo' => 'Registrar Acta de Examen',
        'subtitulo' => 'Datos del Acta',
        'boton' => 'Registrar Acta',
        'urlCancelar' => route('panel.actas.index', ['carrera' => $carrera->id]),
        'carreraNombre' => $carrera->nombre_carrera,
        'acta' => null,
        'tribunal' => [],
        'placeholderDocente' => 'Buscar docente por apellido...',
        'columnaQuitar' => 'Acción',
    ])
@endsection
