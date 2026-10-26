@include('errors.layout', [
    'codigo' => 429,
    'titulo' => 'Demasiados intentos',
    'mensaje' => 'Realizó demasiados intentos seguidos. Espere un minuto e intente de nuevo.',
])
