{{-- CU1 P1 Buscar alumno (Paso_1_Buscar_Alumno.png, Fig. 62). --}}
@extends('layouts.panel')

@section('title', 'Preinscripción · Buscar alumno')

@section('encabezado')
    <x-header-with-title :titulo="\App\Http\Controllers\Panel\Alumnos\PreinscripcionController::ENCABEZADO" />
@endsection

@section('contenido')
    @include('panel.alumnos.preinscripcion._pasos', ['paso' => 1])

    <div class="card las-tarjeta">
        <div class="card-body p-4">
            <h2 class="h4 font-weight-bold mb-2"><i class="fas fa-search text-info mr-2" aria-hidden="true"></i>Buscar alumno</h2>
            <p class="text-muted mb-4">Ingresa el DNI para verificar si ya existe en el sistema.</p>
            <form action="{{ route('panel.preinscripcion.buscar') }}" method="POST" novalidate>
                @csrf
                <div class="form-row align-items-start">
                    <div class="col-12 col-md-6 col-lg-5">
                        <x-form.input name="dni" label="DNI del alumno" :value="$dni" mascara="dni" inputmode="numeric" autofocus
                                      :ayuda="app()->environment('production') ? null : 'Dato de prueba: 30123456 corresponde a un alumno existente.'" />
                    </div>
                    <div class="col-12 col-md-auto las-asistente__buscar">
                        <button type="submit" class="btn btn-las las-btn-principal"><i class="fas fa-search mr-1" aria-hidden="true"></i> Buscar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
