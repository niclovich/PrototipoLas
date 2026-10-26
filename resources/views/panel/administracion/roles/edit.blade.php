@extends('layouts.panel')

@section('title', 'Modificar rol')

@section('encabezado')
    <x-header-with-title titulo="Modificar rol" :subtitulo="$rol->name" />
@endsection

@section('contenido')
    @include('panel.administracion.roles._form', ['accion' => route('panel.roles.update', $rol), 'metodo' => 'PUT'])
@endsection
