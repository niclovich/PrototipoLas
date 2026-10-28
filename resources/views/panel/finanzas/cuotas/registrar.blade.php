{{-- CU 33: P2 Formulario Registrar Cuota (wireframe Registrar_P2.png, Fig. 246). La carrera viene de P1. --}}
@extends('layouts.panel')

@section('title', 'Registrar Cuota')

@section('encabezado')
    <x-header-with-title titulo="GESTIÓN DE CUOTAS" />
@endsection

@section('contenido')
    <form action="{{ route('panel.cuotas.store', $carrera) }}" method="POST" novalidate>
        @csrf
        <div class="card card-las-plano">
            <div class="card-body">
                <h2 class="las-titulo-pantalla">Registrar Cuota</h2>

                <div class="card las-tarjeta">
                    <div class="card-body">
                        <h3 class="las-subtitulo las-subtitulo--tarjeta">Datos de la Cuota</h3>
                        <div class="row">
                            <div class="col-12 col-lg-8">
                                <x-form.input name="carrera_nombre" label="Carrera" :value="$carrera->nombre_carrera" readonly />
                            </div>
                            <div class="w-100"></div>
                            <div class="col-12 col-md-4 col-lg-3">
                                <x-form.select name="mes_id" label="Mes" :opciones="$meses" placeholder="Seleccione un mes" :requerido="true" />
                            </div>
                            <div class="col-12 col-md-4 col-lg-2">
                                <x-form.input type="number" name="anio" label="Año" :value="$anio" step="1" :requerido="true" />
                            </div>
                            <div class="col-12 col-md-4 col-lg-3">
                                <x-form.input name="monto" label="Monto" placeholder="Ej: 45000" inputmode="decimal" :requerido="true" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 px-0 d-flex flex-wrap justify-content-end">
                <a href="{{ route('panel.cuotas.index', ['carrera' => $carrera->id, 'anio' => $anio]) }}" class="btn btn-outline-las las-btn-principal mr-2 mb-2">Cancelar</a>
                <button type="submit" class="btn btn-las las-btn-principal mb-2">Registrar Cuota</button>
            </div>
        </div>
    </form>

    {{-- CU 33 alt. 7, 10 y 13 (orden de la especificación): Aceptar → vuelve al campo. --}}
    <x-modal-aviso-errores :campos="[
        'anio' => 'Año lectivo inválido',
        'mes_id' => 'Cuota existente',
        'monto' => 'Monto inválido',
    ]" :mensajes="[
        'anio' => \App\Services\Finanzas\CuotaService::MENSAJE_ANIO_MENOR,
        'mes_id' => \App\Services\Finanzas\CuotaService::MENSAJE_CUOTA_EXISTENTE,
        'monto' => \App\Services\Finanzas\CuotaService::MENSAJE_MONTO_INVALIDO,
    ]" />
@endsection
