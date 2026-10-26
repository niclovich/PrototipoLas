@extends('layouts.auth')

@section('title', 'Verificar correo electrónico')
@section('auth_header', 'Verificar correo electrónico')

@section('cuerpo')
    @if (session('status') === 'verification-link-sent')
        <div class="alert alert-success" role="alert">Le enviamos un nuevo enlace de verificación a su correo electrónico.</div>
    @endif
    <p>Antes de continuar, revise su correo electrónico y haga clic en el enlace de verificación.</p>
    <form action="{{ route('verification.send') }}" method="POST" class="mb-2">
        @csrf
        <button type="submit" class="btn btn-las btn-block">Reenviar enlace de verificación</button>
    </form>
    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-link btn-block">Cerrar sesión</button>
    </form>
@stop
