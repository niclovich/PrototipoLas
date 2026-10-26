@extends('layouts.panel')

@section('title', 'Modificar - '.$catalogo->titulo)

@section('encabezado')
    <x-header-with-title :titulo="'Modificar '.mb_strtolower($catalogo->singular)" :subtitulo="$catalogo->titulo" />
@endsection

@section('contenido')
    @include('panel.administracion.catalogos._form', [
        'accion' => route('panel.catalogos.registros.update', [$catalogo->clave, $registro->id]),
        'metodo' => 'PUT',
    ])
@endsection
