@extends('layouts.panel')

@section('title', 'Inicio')

@section('contenido')
    <section class="las-inicio-hero" aria-labelledby="titulo-inicio">
        <div class="las-inicio-hero__contenido">
            <p class="las-inicio-hero__fecha mb-2"><i class="far fa-calendar-alt mr-2" aria-hidden="true"></i>{{ ucfirst($fecha) }}</p>
            <h1 id="titulo-inicio">Hola, {{ auth()->user()->nombre_usuario }}</h1>
            <p class="las-inicio-hero__bajada mb-0">
                Bienvenido al espacio de gestión de {{ config('las.instituto_nombre') }}.
            </p>
            <div class="las-inicio-hero__rol">
                <i class="fas fa-shield-alt" aria-hidden="true"></i>
                {{ auth()->user()->nombreRol() ?? 'Sin rol asignado' }}
            </div>
        </div>
        <div class="las-inicio-hero__marca" aria-hidden="true">
            <img src="{{ asset('img/logo-las.svg') }}" alt="">
        </div>
    </section>

    <div class="las-inicio-seccion">
        <div>
            <p class="las-inicio-seccion__eyebrow mb-1">Panel de trabajo</p>
            <h2 class="las-inicio-seccion__titulo mb-1">Accesos principales</h2>
            <p class="text-muted mb-0">Las opciones disponibles corresponden a tu rol y permisos.</p>
        </div>
    </div>

    @if ($accesos->isNotEmpty())
        <div class="row las-accesos" role="list">
            @foreach ($accesos as $acceso)
                <div class="col-12 col-sm-6 col-xl-3 mb-3" role="listitem">
                    <a href="{{ route($acceso['ruta']) }}" class="las-acceso">
                        <span class="las-acceso__icono"><i class="{{ $acceso['icono'] }}" aria-hidden="true"></i></span>
                        <span class="las-acceso__contenido">
                            <strong>{{ $acceso['texto'] }}</strong>
                            <small>Abrir módulo</small>
                        </span>
                        <i class="fas fa-chevron-right las-acceso__flecha" aria-hidden="true"></i>
                    </a>
                </div>
            @endforeach
        </div>
    @else
        <div class="las-estado-vacio">
            <span class="las-estado-vacio__icono"><i class="fas fa-lock" aria-hidden="true"></i></span>
            <h2 class="h5">No hay módulos disponibles</h2>
            <p class="mb-0 text-muted">Solicitá al administrador que revise los permisos de tu cuenta.</p>
        </div>
    @endif

    <aside class="las-inicio-ayuda" aria-label="Ayuda de navegación">
        <span class="las-inicio-ayuda__icono"><i class="far fa-lightbulb" aria-hidden="true"></i></span>
        <div>
            <strong>Todo listo para comenzar</strong>
            <p class="mb-0">También podés usar el menú lateral para recorrer todas las funciones habilitadas.</p>
        </div>
    </aside>
@endsection
