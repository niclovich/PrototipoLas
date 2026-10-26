@extends('layouts.auth')

@section('title', 'Recuperar contraseña')
@section('auth_header', 'Recuperar contraseña')

@section('cuerpo')
    <p class="text-muted">Ingrese su correo electrónico y le enviaremos un enlace para restablecer la contraseña.</p>
    <form action="{{ route('password.email') }}" method="POST" novalidate>
        @csrf
        <x-form.input name="email" type="email" label="Correo electrónico" :requerido="true" autocomplete="username" autofocus />
        <button type="submit" class="btn btn-las btn-block">
            <i class="fas fa-paper-plane" aria-hidden="true"></i> Enviar enlace
        </button>
    </form>
@stop

@section('auth_footer')
    <a href="{{ route('login') }}">Volver a iniciar sesión</a>
@stop
