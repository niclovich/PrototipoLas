{{-- CU 3: P2 Registrar Carrera. --}}
@extends('layouts.panel')

@section('title', 'Registrar Carrera')

@section('encabezado')
    <x-header-with-title titulo="GESTIÓN DE CARRERAS" />
@endsection

@section('contenido')
    @include('panel.academico.carreras._form', [
        'modo' => 'registrar',
        'accion' => route('panel.carreras.store'),
        'metodo' => 'POST',
        'titulo' => 'Registrar Carrera',
        'boton' => 'Registrar Carrera',
    ])
@endsection
