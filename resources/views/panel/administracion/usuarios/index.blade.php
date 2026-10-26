@extends('layouts.panel')

@section('title', 'Usuarios')

@section('encabezado')
    <x-header-with-title titulo="Usuarios">
        <x-btn-accion :href="route('panel.usuarios.create')" icono="fas fa-plus" texto="Nuevo usuario" color="light"
                      tamanio="md" :conTexto="true" permiso="crear_usuario" />
    </x-header-with-title>
@endsection

@section('contenido')
    @include('panel._pestanias-estado', [
        'id' => 'usuarios',
        'url' => route('panel.usuarios.datatable'),
        'columnas' => [
            ['data' => 'apellido_nombre', 'titulo' => 'Apellido y Nombre', 'prioridad' => 1],
            ['data' => 'dni_usuario', 'titulo' => 'DNI', 'prioridad' => 3],
            ['data' => 'email', 'titulo' => 'Correo electrónico', 'prioridad' => 4],
            ['data' => 'rol', 'titulo' => 'Rol', 'orderable' => false, 'prioridad' => 5],
            ['data' => 'estado', 'titulo' => 'Estado', 'orderable' => false, 'searchable' => false, 'prioridad' => 6],
            ['data' => 'acciones', 'titulo' => 'Acciones', 'orderable' => false, 'searchable' => false, 'clase' => 'text-nowrap', 'prioridad' => 2],
        ],
    ])

@endsection
