@php
    $usuario = Auth::user();
    $logoutUrl = config('adminlte.use_route_url', false)
        ? route(config('adminlte.logout_url', 'logout'))
        : url(config('adminlte.logout_url', 'logout'));
@endphp

<li class="nav-item dropdown user-menu las-user-menu">
    <button type="button" class="nav-link dropdown-toggle las-user-menu__toggle" data-toggle="dropdown"
            aria-haspopup="true" aria-expanded="false" aria-label="Abrir menú de {{ $usuario->name }}">
        <x-user-avatar :usuario="$usuario" tamano="sm" />
        <span class="las-user-menu__nombre d-none d-sm-flex">
            <strong>{{ $usuario->name }}</strong>
            <small>{{ $usuario->nombreRol() ?? 'Sin rol' }}</small>
        </span>
        <i class="fas fa-chevron-down las-user-menu__chevron" aria-hidden="true"></i>
    </button>

    <div class="dropdown-menu dropdown-menu-right las-user-menu__dropdown">
        <div class="las-user-menu__cabecera">
            <x-user-avatar :usuario="$usuario" tamano="lg" />
            <div class="min-w-0">
                <strong>{{ $usuario->name }}</strong>
                <span>{{ $usuario->email }}</span>
            </div>
        </div>
        <div class="las-user-menu__rol">
            <span><i class="fas fa-shield-alt" aria-hidden="true"></i> {{ $usuario->nombreRol() ?? 'Sin rol asignado' }}</span>
            <span class="las-user-menu__estado"><i class="fas fa-circle" aria-hidden="true"></i> Cuenta activa</span>
        </div>
        <div class="las-user-menu__acciones">
            <a href="{{ route('panel.inicio') }}" class="dropdown-item">
                <span class="las-user-menu__accion-icono"><i class="fas fa-home" aria-hidden="true"></i></span>
                <span><strong>Ir al inicio</strong><small>Volver al panel principal</small></span>
            </a>
            <form action="{{ $logoutUrl }}" method="POST">
                @csrf
                @if (config('adminlte.logout_method'))
                    @method(config('adminlte.logout_method'))
                @endif
                <button type="submit" class="dropdown-item las-user-menu__salir">
                    <span class="las-user-menu__accion-icono"><i class="fas fa-sign-out-alt" aria-hidden="true"></i></span>
                    <span><strong>Cerrar sesión</strong><small>Salir de forma segura</small></span>
                </button>
            </form>
        </div>
    </div>
</li>
