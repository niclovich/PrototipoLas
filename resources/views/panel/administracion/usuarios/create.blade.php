@extends('layouts.panel')

@section('title', 'Nuevo usuario')

@section('encabezado')
    <x-header-with-title titulo="Nuevo usuario" />
@endsection

@section('contenido')
    @include('panel.administracion.usuarios._form', ['accion' => route('panel.usuarios.store'), 'metodo' => 'POST'])
@endsection
