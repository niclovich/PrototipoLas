{{-- P2 Modificar Materia. --}}
@extends('layouts.panel')

@section('title', 'Modificar Materia')

@section('encabezado')
    <x-header-with-title titulo="GESTIÓN DE MATERIAS" />
@endsection

@section('contenido')
    @include('panel.academico.materias._form', [
        'modo' => 'modificar',
        'accion' => route('panel.materias.update', $materia),
        'metodo' => 'PUT',
        'titulo' => 'Modificar Materia',
        'boton' => 'Guardar Cambios',
    ])
@endsection
