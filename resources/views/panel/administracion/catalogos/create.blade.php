@extends('layouts.panel')

@section('title', 'Nuevo registro - '.$catalogo->titulo)

@section('encabezado')
    <x-header-with-title :titulo="$catalogo->textoNuevo()" :subtitulo="$catalogo->titulo" />
@endsection

@section('contenido')
    @include('panel.administracion.catalogos._form', [
        'accion' => route('panel.catalogos.registros.store', $catalogo->clave),
        'metodo' => 'POST',
    ])
@endsection
