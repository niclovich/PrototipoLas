{{-- CU1 P3 Encuesta socioeconomica (Paso_3_Encuesta.png, Fig. 64) = CU 9 pasos 4–5. Nada es obligatorio (D2 de E3). --}}
@extends('layouts.panel')

@section('title', 'Preinscripción · Encuesta')

@section('encabezado')
    <x-header-with-title :titulo="\App\Http\Controllers\Panel\Alumnos\PreinscripcionController::ENCABEZADO" />
@endsection

@php
    $secciones = \App\Models\EncuestaSocioeconomica::SECCIONES;
@endphp

@section('contenido')
    @include('panel.alumnos.preinscripcion._pasos', ['paso' => 3])

    <form action="{{ route('panel.preinscripcion.encuesta.store') }}" method="POST" novalidate>
        @csrf
        <h2 class="h4 font-weight-bold mb-1">Encuesta socioeconomica</h2>
        <p class="text-muted">Marca solo los datos que correspondan.</p>

        <div class="row">
            <div class="col-12 col-lg-6">
                <h3 class="las-subtitulo">GRUPO FAMILIAR</h3>
                <div class="las-casillas">
                    @foreach ($secciones['GRUPO FAMILIAR'] as $campo => $texto)
                        <x-form.checkbox :name="$campo" :label="$texto" :checked="(bool) ($encuesta[$campo] ?? false)" grupo-clase="las-casilla" />
                    @endforeach
                </div>
                <div class="row mt-3">
                    <div class="col-12 col-sm-6">
                        <x-form.select name="nivel_estudios_madre" label="Estudios de la madre" :opciones="$niveles" :value="$encuesta['nivel_estudios_madre'] ?? null" />
                    </div>
                    <div class="col-12 col-sm-6">
                        <x-form.select name="nivel_estudios_padre" label="Estudios del padre" :opciones="$niveles" :value="$encuesta['nivel_estudios_padre'] ?? null" />
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <h3 class="las-subtitulo">SITUACION ECONOMICA</h3>
                <div class="las-casillas">
                    @foreach ($secciones['SITUACION ECONOMICA'] as $campo => $texto)
                        <x-form.checkbox :name="$campo" :label="$texto" :checked="(bool) ($encuesta[$campo] ?? false)" grupo-clase="las-casilla" />
                    @endforeach
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-lg-6">
                <h3 class="las-subtitulo">IDIOMAS Y TECNOLOGIA</h3>
                <x-form.select name="idiomas[]" id="idiomas" label="Idiomas que habla" :opciones="$idiomas" :value="$encuesta['idiomas'] ?? []"
                               :multiple="true" :select2="true" placeholder="Ej: Espanol, Guarani" />
                <div class="las-casillas">
                    @foreach ($secciones['IDIOMAS Y TECNOLOGIA'] as $campo => $texto)
                        <x-form.checkbox :name="$campo" :label="$texto" :checked="(bool) ($encuesta[$campo] ?? false)" grupo-clase="las-casilla" />
                    @endforeach
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <h3 class="las-subtitulo">DIVERSIDAD Y ACCESIBILIDAD</h3>
                <div class="las-casillas">
                    @foreach ($secciones['DIVERSIDAD Y ACCESIBILIDAD'] as $campo => $texto)
                        <x-form.checkbox :name="$campo" :label="$texto" :checked="(bool) ($encuesta[$campo] ?? false)" grupo-clase="las-casilla" />
                    @endforeach
                </div>
            </div>
        </div>

        @include('panel.alumnos.preinscripcion._botones', ['urlAtras' => route('panel.preinscripcion.datos-personales')])
    </form>
@endsection
