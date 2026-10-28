{{-- CU 4: P2 Modificar Carrera. --}}
@extends('layouts.panel')

@section('title', 'Modificar Carrera')

@section('encabezado')
    <x-header-with-title titulo="GESTIÓN DE CARRERAS" />
@endsection

@section('contenido')
    @include('panel.academico.carreras._form', [
        'modo' => 'modificar',
        'accion' => route('panel.carreras.update', $carrera),
        'metodo' => 'PUT',
        'titulo' => 'Modificar Carrera',
        'boton' => 'Guardar Cambios',
    ])
@endsection
