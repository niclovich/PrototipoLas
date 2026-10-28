{{-- CU 5: P2 Formulario de Registro de Plan de Estudio (Registrar_Plan_P1b.png). Botón primario primero, como el PNG. --}}
@extends('layouts.panel')

@section('title', 'Registrar Plan de Estudio')

@section('encabezado')
    <x-header-with-title titulo="GESTIÓN DE PLAN DE ESTUDIO" />
@endsection

@section('contenido')
    <form action="{{ route('panel.planes.store', $carrera) }}" method="POST" novalidate>
        @csrf
        <div class="card card-las-plano">
            <div class="card-body">
                <h2 class="las-titulo-pantalla">Registrar Plan de Estudio</h2>

                <div class="card las-tarjeta">
                    <div class="card-body">
                        <h3 class="las-subtitulo las-subtitulo--tarjeta">1 · Datos del Plan</h3>
                        <div class="row">
                            <div class="col-12">
                                <x-form.input name="carrera_nombre" label="Carrera" :value="$carrera->nombre_carrera" readonly />
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <x-form.input type="number" name="anio_plan_estudio" label="Año del Plan de Estudio" :value="now()->year"
                                              :requerido="true" min="{{ \App\Services\Academico\PlanEstudioService::ANIO_MINIMO }}"
                                              max="{{ now()->year + 1 }}" step="1" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 px-0 d-flex flex-wrap justify-content-end">
                <button type="submit" class="btn btn-las las-btn-principal mr-2 mb-2">Registrar Plan de Estudio</button>
                <a href="{{ route('panel.planes.index', ['carrera' => $carrera->id]) }}" class="btn btn-outline-las las-btn-principal mb-2">Cancelar</a>
            </div>
        </div>
    </form>

    {{-- CU 5 alt. 7: 7.2 Aceptar → 7.3 vuelve al paso 6 (foco en el año). --}}
    <x-modal-aviso-errores :campos="['anio_plan_estudio' => 'Año inválido']" />
@endsection
