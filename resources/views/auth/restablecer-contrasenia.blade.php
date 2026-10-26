@extends('layouts.auth')

@section('title', 'Restablecer contraseña')
@section('auth_header', 'Restablecer contraseña')

@section('cuerpo')
    <form action="{{ route('password.update') }}" method="POST" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <x-form.input name="email" type="email" label="Correo electrónico" :value="$request->email" :requerido="true" autocomplete="username" />
        @include('auth._campos-contrasenia')
        <button type="submit" class="btn btn-las btn-block">
            <i class="fas fa-key" aria-hidden="true"></i> Restablecer contraseña
        </button>
    </form>
@stop
