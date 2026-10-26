{{-- Pantalla "Confirmación" de los wireframes: mensaje y botón Aceptar. El slot admite contenido adicional. --}}
@props(['titulo' => 'Confirmación', 'mensaje', 'urlAceptar', 'textoBoton' => 'Aceptar'])
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card card-outline card-las text-center">
            <div class="card-body py-5">
                <i class="fas fa-check-circle fa-3x text-success mb-3" aria-hidden="true"></i>
                <h2 class="h4">{{ $titulo }}</h2>
                <p class="mb-4">{{ $mensaje }}</p>
                {{ $slot }}
                <a href="{{ $urlAceptar }}" class="btn btn-las px-5">{{ $textoBoton }}</a>
            </div>
        </div>
    </div>
</div>
