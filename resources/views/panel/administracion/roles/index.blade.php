@extends('layouts.panel')

@section('title', 'Roles y Permisos')

@section('encabezado')
    <x-header-with-title titulo="Roles y Permisos">
        <x-btn-accion :href="route('panel.roles.create')" icono="fas fa-plus" texto="Nuevo rol" color="light"
                      tamanio="md" :conTexto="true" permiso="crear_rol" />
    </x-header-with-title>
@endsection

@section('contenido')
    @include('panel._pestanias-estado', [
        'id' => 'roles',
        'url' => route('panel.roles.datatable'),
        'columnas' => [
            ['data' => 'name', 'titulo' => 'Rol', 'prioridad' => 1],
            ['data' => 'descripcion', 'titulo' => 'Descripción', 'prioridad' => 5],
            ['data' => 'permissions_count', 'titulo' => 'Permisos', 'searchable' => false, 'clase' => 'text-center', 'prioridad' => 3],
            ['data' => 'users_count', 'titulo' => 'Usuarios', 'searchable' => false, 'clase' => 'text-center', 'prioridad' => 4],
            ['data' => 'estado', 'titulo' => 'Estado', 'orderable' => false, 'searchable' => false, 'prioridad' => 6],
            ['data' => 'acciones', 'titulo' => 'Acciones', 'orderable' => false, 'searchable' => false, 'clase' => 'text-nowrap', 'prioridad' => 2],
        ],
    ])

@endsection
