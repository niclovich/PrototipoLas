@extends('layouts.panel')

@section('title', $catalogo->titulo)

@section('encabezado')
    <x-header-with-title :titulo="$catalogo->titulo">
        <x-btn-accion :href="route('panel.catalogos.index')" icono="fas fa-arrow-left" texto="Catálogos" color="light" tamanio="md" :conTexto="true" />
        <x-btn-accion :href="route('panel.catalogos.registros.create', $catalogo->clave)" icono="fas fa-plus" :texto="'Nuevo/a '.mb_strtolower($catalogo->singular)"
                      color="light" tamanio="md" :conTexto="true" permiso="crear_catalogo" />
    </x-header-with-title>
@endsection

@section('contenido')
    @include('panel._pestanias-estado', [
        'id' => 'catalogo',
        'url' => route('panel.catalogos.registros.datatable', $catalogo->clave),
        'columnas' => $catalogo->columnasTabla(),
        'orden' => [[$catalogo->indiceColumnaPrincipal(), 'asc']],
    ])
@endsection
