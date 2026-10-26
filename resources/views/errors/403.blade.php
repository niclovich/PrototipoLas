@include('errors.layout', [
    'codigo' => 403,
    'titulo' => 'Acceso denegado',
    'mensaje' => $mensaje ?? 'No tiene permiso para acceder a esta sección.',
])
