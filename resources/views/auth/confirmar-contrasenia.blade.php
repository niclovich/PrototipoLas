@extends('layouts.auth')

@section('title', 'Confirmar contraseña')
@section('auth_header', 'Confirme su contraseña para continuar')

@section('cuerpo')
    <form action="{{ route('password.confirm') }}" method="POST" novalidate>
        @csrf
        <x-form.input name="password" type="password" label="Contraseña" :requerido="true" autocomplete="current-password" autofocus />
        <button type="submit" class="btn btn-las btn-block">Confirmar</button>
    </form>
@stop
