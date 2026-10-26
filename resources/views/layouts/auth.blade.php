{{-- Layout de las pantallas de acceso (login, recuperación, verificación, definir contraseña) con estilo AdminLTE. --}}
@extends('adminlte::master')

@section('classes_body', 'login-page las-auth-page')

@section('adminlte_css')
    @vite('resources/css/app.css')
    @stack('css')
    @yield('css')
@stop

@section('body')
    <div class="container-fluid las-auth-shell">
        <div class="row no-gutters min-vh-100">
            <section class="col-md-7 d-none d-md-block las-auth-hero" aria-labelledby="las-auth-instituto">
                <div id="loginCarousel" class="carousel slide h-100" data-ride="carousel" data-interval="5000" aria-hidden="true">
                    <div class="carousel-inner h-100">
                        @foreach (['login-01.jpg', 'login-02.jpg', 'login-03.jpg'] as $imagen)
                            <div @class(['carousel-item', 'h-100', 'active' => $loop->first])>
                                <img src="{{ asset('img/auth/'.$imagen) }}" class="d-block w-100 h-100" alt="">
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="las-auth-hero__overlay"></div>
                <div class="las-auth-hero__contenido">
                    <h1 id="las-auth-instituto">{{ config('las.instituto_nombre') }}</h1>
                    <p>Administración y gestión de los alumnos del Instituto Superior del Milagro N.º 8207.</p>
                </div>
            </section>

            <main class="col-md-5 col-12 las-auth-panel">
                <div class="login-box las-auth-box">
                    <div class="login-logo las-auth-logo">
                        <a href="{{ route('login') }}" aria-label="Ir al inicio de sesión">
                            <img src="{{ asset('img/auth/logo-principal.png') }}"
                                 alt="Logo de {{ config('las.instituto_nombre') }}">
                            <span>Ingreso al sistema</span>
                        </a>
                    </div>

                    <div class="login-card-body las-auth-formulario">
                        @hasSection('auth_header')
                            <h2 class="las-auth-titulo">@yield('auth_header')</h2>
                        @endif

                        <x-alert />
                        @yield('cuerpo')

                        @hasSection('auth_footer')
                            <div class="las-auth-footer">
                                @yield('auth_footer')
                            </div>
                        @endif
                    </div>
                </div>
            </main>
        </div>
    </div>
@stop

@section('adminlte_js')
    @vite('resources/js/app.js')
    @stack('js')
    @yield('js')
@stop
