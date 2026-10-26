@extends('layouts.panel')

@section('title', 'Inicio')

@section('encabezado')
    <x-header-with-title titulo="Inicio" />
@endsection

@section('contenido')
    <div class="card card-outline card-las">
        <div class="card-body">
            <h2 class="h5">Bienvenido/a, {{ auth()->user()->nombre_usuario }}</h2>
            <p class="mb-0 text-muted">
                {{ config('las.instituto_nombre') }} &middot; Rol: {{ auth()->user()->nombreRol() ?? 'sin rol asignado' }}.
                Use el menú lateral para acceder a sus funciones.
            </p>
        </div>
    </div>
@endsection
