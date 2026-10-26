@extends('layouts.auth')

@section('title', 'Definir contraseña')
@section('auth_header', 'Definir contraseña')

@section('cuerpo')
    <p class="text-muted">
        Hola, {{ $usuario->nombre_usuario }}. Defina la contraseña para ingresar con <strong>{{ $usuario->email }}</strong>.
    </p>
    <form action="{{ $accion }}" method="POST" novalidate>
        @csrf
        @include('auth._campos-contrasenia')
        <button type="submit" class="btn btn-las btn-block">
            <i class="fas fa-check" aria-hidden="true"></i> Guardar contraseña
        </button>
    </form>
@stop
