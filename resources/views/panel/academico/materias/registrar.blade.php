{{-- P2 Registrar Materia. --}}
@extends('layouts.panel')

@section('title', 'Registrar Materia')

@section('encabezado')
    <x-header-with-title titulo="GESTIÓN DE MATERIAS" />
@endsection

@section('contenido')
    @include('panel.academico.materias._form', [
        'modo' => 'registrar',
        'accion' => route('panel.materias.store'),
        'metodo' => 'POST',
        'titulo' => 'Registrar Materia',
        'boton' => 'Registrar Materia',
    ])
@endsection
