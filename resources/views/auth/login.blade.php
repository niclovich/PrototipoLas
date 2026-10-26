@extends('layouts.auth')

@section('title', 'Iniciar sesión')
@section('auth_header', 'Ingrese con su correo electrónico y contraseña')

@section('cuerpo')
    <form action="{{ route('login') }}" method="POST" novalidate>
        @csrf
        <div class="form-group">
            <label class="sr-only" for="email">Correo electrónico</label>
            <div class="input-group">
                <input id="email" type="email" name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" placeholder="Correo electrónico"
                       autocomplete="username" required aria-required="true" autofocus
                       @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                <div class="input-group-append">
                    <div class="input-group-text"><span class="fas fa-envelope" aria-hidden="true"></span></div>
                </div>
                @error('email')
                    <span id="email-error" class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>

        <div class="form-group">
            <label class="sr-only" for="password">Contraseña</label>
            <div class="input-group">
                <input id="password" type="password" name="password"
                       class="form-control @error('password') is-invalid @enderror"
                       placeholder="Contraseña" autocomplete="current-password"
                       required aria-required="true"
                       @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                <div class="input-group-append">
                    <div class="input-group-text"><span class="fas fa-lock" aria-hidden="true"></span></div>
                </div>
                @error('password')
                    <span id="password-error" class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>

        <x-form.checkbox name="remember" label="Recordarme" grupoClase="mb-3" />

        <button type="submit" class="btn btn-las btn-block las-auth-ingresar">
            <i class="fas fa-sign-in-alt" aria-hidden="true"></i> Ingresar
        </button>
    </form>
@stop

@section('auth_footer')
    <a href="{{ route('password.request') }}">¿Olvidó su contraseña?</a>
    <p class="las-auth-direccion">Alvarado 951, Salta Capital</p>
@stop
