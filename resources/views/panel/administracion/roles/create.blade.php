@extends('layouts.panel')

@section('title', 'Nuevo rol')

@section('encabezado')
    <x-header-with-title titulo="Nuevo rol" />
@endsection

@section('contenido')
    @include('panel.administracion.roles._form', ['accion' => route('panel.roles.store'), 'metodo' => 'POST'])
@endsection
