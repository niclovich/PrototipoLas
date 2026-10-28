{{-- CU1 P5 Confirmar preinscripcion (Paso_5_Confirmar.png, Fig. 66): pasos 22–24. Alt. 18.1 en modal al llegar. --}}
@extends('layouts.panel')

@section('title', 'Preinscripción · Confirmar')

@section('encabezado')
    <x-header-with-title :titulo="\App\Http\Controllers\Panel\Alumnos\PreinscripcionController::ENCABEZADO" />
@endsection

@php
    $texto = fn ($valor) => $valor instanceof \BackedEnum ? $valor->value : ($valor === null || $valor === '' ? '—' : $valor);
    $fecha = ! empty($persona['fecha_nacimiento']) ? \Illuminate\Support\Carbon::parse($persona['fecha_nacimiento'])->format('d/m/Y') : null;
    $secciones = [
        'DATOS PERSONALES' => [
            'Apellido' => $persona['apellido_usuario'] ?? null,
            'Nombre' => $persona['nombre_usuario'] ?? null,
            'Fecha de nacimiento' => $fecha,
            'Sexo' => $persona['sexo'] ?? null,
            'Estado civil' => $persona['estado_civil'] ?? null,
        ],
        'CONTACTO' => [
            'Correo' => $persona['email'] ?? null,
            'Telefono' => $persona['telefono_usuario'] ?? null,
            'Celular' => $persona['celular_usuario'] ?? null,
        ],
        'DOMICILIO' => [
            'Direccion' => $persona['direccion_usuario'] ?? null,
            'Provincia' => $localidad?->provincia?->provincia,
            'Localidad' => $localidad?->localidad,
        ],
        'CARRERA' => [
            'Carrera' => $carrera?->nombre_carrera,
            'Secundario' => $seleccion['situacion_estudios'],
            'Adeuda materias' => $seleccion['adeuda_materia'] ? 'Si' : 'No',
            // E12-D1: paso 22 de la spec.
            'Constancia de Titulo' => $constanciaTitulo ? 'Si' : 'No',
            'Observaciones' => $seleccion['observaciones'] ?? null,
        ],
        // Como en el wireframe: solo las respuestas afirmativas.
        'ENCUESTA SOCIOECONOMICA' => collect(\App\Models\EncuestaSocioeconomica::SECCIONES)->collapse()
            ->filter(fn ($etiqueta, $campo) => (bool) ($encuesta[$campo] ?? false))
            ->mapWithKeys(fn ($etiqueta) => [$etiqueta => 'Si'])->all(),
    ];
@endphp

@section('contenido')
    @include('panel.alumnos.preinscripcion._pasos', ['paso' => 5])

    <form action="{{ route('panel.preinscripcion.store') }}" method="POST" novalidate>
        @csrf
        <h2 class="h4 font-weight-bold mb-3">Confirmar preinscripcion</h2>
        <div class="d-flex align-items-center mb-4">
            <span class="las-avatar mr-3" aria-hidden="true"></span>
            <div>
                <div class="h4 font-weight-bold mb-0">{{ trim(($persona['nombre_usuario'] ?? '').' '.($persona['apellido_usuario'] ?? '')) }}</div>
                <div class="text-muted">DNI {{ $dni }} - {{ $nuevo ? 'Nuevo alumno' : 'Alumno registrado' }}</div>
            </div>
        </div>

        <div class="row">
            @foreach ($secciones as $titulo => $filas)
                <div class="col-12 col-lg-6 mb-3">
                    <h3 class="las-subtitulo">{{ $titulo }}</h3>
                    <dl class="las-resumen mb-0">
                        @foreach ($filas as $etiqueta => $valor)
                            <div class="d-flex justify-content-between">
                                <dt class="font-weight-normal text-muted">{{ $etiqueta }}</dt>
                                <dd class="font-weight-bold text-right mb-1">{{ $texto($valor) }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            @endforeach
        </div>

        @include('panel.alumnos.preinscripcion._botones', ['urlAtras' => route('panel.preinscripcion.carrera'), 'textoBoton' => 'Confirmar preinscripcion'])
    </form>

    <x-modal-aviso id="modal-documentacion" titulo="Documentación no adjuntada" :mensaje="\App\Services\Alumnos\PreinscripcionService::MENSAJE_DOCUMENTACION_NO_ADJUNTADA"
                   :abrir="(bool) session(\App\Http\Controllers\Panel\Alumnos\PreinscripcionController::AVISO_DOCUMENTACION)" />
@endsection
