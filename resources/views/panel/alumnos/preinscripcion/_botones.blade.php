{{-- Botones "← Atras" y principal de los pasos 2 a 5. Variables: $urlAtras; opcional $textoBoton (por defecto "Continuar →"). --}}
<div class="d-flex flex-wrap justify-content-between mt-4">
    <a href="{{ $urlAtras }}" class="btn btn-outline-las las-btn-principal mb-2">&larr; Atras</a>
    <button type="submit" class="btn btn-las las-btn-principal mb-2">{{ $textoBoton ?? 'Continuar →' }}</button>
</div>
