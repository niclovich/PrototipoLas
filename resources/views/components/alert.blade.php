{{-- Único bloque de mensajes flash del sistema (success, error, warning, info, status) y errores de acciones ('accion'). --}}
@php
    $tipos = [
        'success' => ['success', 'fa-check-circle'],
        'status' => ['success', 'fa-check-circle'],
        'error' => ['danger', 'fa-ban'],
        'warning' => ['warning', 'fa-exclamation-triangle'],
        'info' => ['info', 'fa-info-circle'],
    ];
    $mensajes = collect($tipos)
        ->filter(fn ($config, $clave) => session()->has($clave))
        ->map(fn ($config, $clave) => [...$config, session($clave)])
        // Fortify usa este código como "status"; la vista de verificación muestra su propio texto.
        ->reject(fn ($mensaje) => $mensaje[2] === 'verification-link-sent');
    if ($errors->has('accion')) {
        $mensajes->put('accion', ['danger', 'fa-ban', $errors->first('accion')]);
    }
@endphp
@foreach ($mensajes as [$color, $icono, $texto])
    <div {{ $attributes->merge(['class' => "alert alert-{$color} alert-dismissible fade show"]) }} role="alert">
        <i class="icon fas {{ $icono }}" aria-hidden="true"></i> {{ $texto }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
    </div>
@endforeach
