{{-- CU2 P2 Formulario de Registro de Inscripción (CU2_inscripcion/wireframes/P2.png, Fig. 69): pasos 5–21. --}}
@extends('layouts.panel')

@section('title', 'Inscripción')

@section('encabezado')
    <x-header-with-title :titulo="\App\Http\Controllers\Panel\Alumnos\InscripcionController::ENCABEZADO" />
@endsection

@section('contenido')
    @include('panel.alumnos.preinscripcion._pasos', ['paso' => 2, 'pasos' => ['Buscar alumno', 'Datos y documentacion'], 'etiqueta' => 'Pasos de la inscripción'])

    <form action="{{ route('panel.inscripciones.store', $alumno) }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf
        <h3 class="las-subtitulo mt-2">DATOS DEL ALUMNO</h3>
        <div class="row">
            <div class="col-12 col-md-3">
                <x-form.input name="dni_alumno" label="DNI" :value="$alumno->usuario->dni_usuario" readonly />
            </div>
            <div class="col-12 col-md-4">
                <x-form.input name="apellido_nombre" label="Apellido y Nombre" :value="$alumno->usuario->nombre_completo" readonly />
            </div>
            <div class="col-12 col-md-5">
                <x-form.input name="carrera_preinscripta" label="Carrera Preinscripta" :value="$alumno->carrera->nombre_carrera" readonly />
            </div>
        </div>

        <h3 class="las-subtitulo mt-3">DOCUMENTACION (obligatoria, formato PDF)</h3>
        @foreach (\App\Services\Alumnos\InscripcionService::DOCUMENTOS as $campo => $tipo)
            <div class="card las-tarjeta mb-2" data-adjunto>
                <div class="card-body d-flex flex-wrap align-items-center">
                    <i class="far fa-file-alt fa-2x text-muted mr-3" aria-hidden="true"></i>
                    <div class="flex-grow-1 mr-3">
                        <div class="font-weight-bold">{{ $campo === 'documento_titulo' ? 'Certificado de Titulo' : 'Fotocopia de DNI' }}</div>
                        <div class="las-ayuda font-italic">Formato PDF</div>
                        <div class="small text-success mt-1 @unless (isset($presentados[$campo])) d-none @endunless" data-adjunto-nombre>
                            @isset($presentados[$campo]) Adjuntado en la preinscripción. @endisset
                        </div>
                        @error($campo)
                            <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                        @enderror
                    </div>
                    <label for="{{ $campo }}" class="btn btn-outline-las mb-0 mt-2 mt-md-0" tabindex="0">
                        <i class="far fa-file-alt mr-1" aria-hidden="true"></i> Adjuntar PDF
                    </label>
                    <input type="file" name="{{ $campo }}" id="{{ $campo }}" class="las-input-oculto" accept="application/pdf" data-adjunto-input>
                </div>
            </div>
        @endforeach

        <h3 class="las-subtitulo mt-4">CUOTAS</h3>
        <div class="d-flex flex-wrap align-items-center mb-2">
            <span class="mr-4" style="min-width: 10rem">{{ $cuota ? \App\Models\Mes::INSCRIPCION.' '.$cuota->anio : \App\Models\Mes::INSCRIPCION }}</span>
            @if ($cuotaPagada)
                <span class="las-estado-cuota las-estado-cuota--pagada">&check; Pagada</span>
            @else
                <span class="las-estado-cuota las-estado-cuota--pendiente">! Pendiente</span>
            @endif
        </div>
        <p class="las-ayuda font-italic">Si el alumno tiene cuotas pendientes, no se puede continuar con la inscripcion</p>

        <div class="d-flex flex-wrap justify-content-between mt-4">
            <a href="{{ route('panel.alumnos.index') }}" class="btn btn-outline-las las-btn-principal mb-2">&larr; Atras</a>
            <button type="submit" class="btn btn-las las-btn-principal mb-2">Confirmar Inscripcion</button>
        </div>
    </form>

    {{-- Alt. 7.1 / 11.1: "Formato Incompatible" → Aceptar → vuelve a la tarjeta del documento. --}}
    <x-modal-aviso-errores :campos="['documento_titulo' => 'Certificado de Titulo', 'documento_dni' => 'Fotocopia de DNI']"
                           :mensajes="['documento_titulo' => \App\Services\Alumnos\InscripcionService::MENSAJE_FORMATO, 'documento_dni' => \App\Services\Alumnos\InscripcionService::MENSAJE_FORMATO]" />
@endsection
