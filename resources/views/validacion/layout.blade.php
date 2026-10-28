{{-- Layout de las validaciones públicas por QR (CU7 y CU5): tarjeta con título y datos mínimos. --}}
@extends('adminlte::master')

@section('title', $tituloPagina)

@section('adminlte_css')
    @vite('resources/css/app.css')
@stop

@section('classes_body', 'hold-transition las-error-page')

@section('body')
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card card-outline card-las">
                    <div class="card-body">
                        <h1 class="h4 text-las mb-3"><i class="fas fa-check-circle" aria-hidden="true"></i> {{ $tituloTarjeta }}</h1>
                        <dl class="row mb-0">
                            @yield('datos')
                        </dl>
                        <p class="text-muted small mt-3 mb-0">{{ config('las.instituto_nombre') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
@stop
