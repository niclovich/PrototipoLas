{{-- Páginas de error en español con estilo AdminLTE (sirven con o sin sesión iniciada). --}}
@extends('adminlte::master')

@section('title', $codigo.' - '.$titulo)

@section('adminlte_css')
    @vite('resources/css/app.css')
@stop

@section('classes_body', 'hold-transition las-error-page')

@section('body')
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card card-outline card-las text-center">
                    <div class="card-body py-5">
                        <p class="display-4 text-las font-weight-bold mb-2">{{ $codigo }}</p>
                        <h1 class="h4 mb-3">{{ $titulo }}</h1>
                        <p class="text-muted mb-4">{{ $mensaje }}</p>
                        @auth
                            <a href="{{ route('panel.inicio') }}" class="btn btn-las"><i class="fas fa-home" aria-hidden="true"></i> Ir al inicio</a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-las"><i class="fas fa-sign-in-alt" aria-hidden="true"></i> Iniciar sesión</a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </main>
@stop
