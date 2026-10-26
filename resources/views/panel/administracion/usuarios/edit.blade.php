@extends('layouts.panel')

@section('title', 'Modificar usuario')

@section('encabezado')
    <x-header-with-title titulo="Modificar usuario" :subtitulo="$usuario->nombre_completo" />
@endsection

@section('contenido')
    @include('panel.administracion.usuarios._form', ['accion' => route('panel.usuarios.update', $usuario), 'metodo' => 'PUT'])
@endsection
