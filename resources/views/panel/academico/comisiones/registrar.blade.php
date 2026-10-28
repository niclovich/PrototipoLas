{{-- CU 17: P2 Formulario Registrar Comisión (wireframe P2.png, Fig. 228). La carrera viene de P1. --}}
@extends('layouts.panel')

@section('title', 'Registrar Comisión')

@section('encabezado')
    <x-header-with-title titulo="GESTIÓN DE COMISIONES" />
@endsection

@section('contenido')
    @php($anioElegido = (int) old('anio_curso'))
    <form action="{{ route('panel.comisiones.store', $carrera) }}" method="POST" novalidate>
        @csrf
        <div class="card card-las-plano">
            <div class="card-body">
                <h2 class="las-titulo-pantalla">Registrar Comisión</h2>

                <div class="card las-tarjeta">
                    <div class="card-body">
                        <h3 class="las-subtitulo las-subtitulo--tarjeta">1 · Datos de la Comisión</h3>
                        <div class="row">
                            <div class="col-12">
                                <x-form.input name="carrera_nombre" label="Carrera" :value="$carrera->nombre_carrera" readonly />
                            </div>
                            <div class="col-12 col-md-4">
                                <x-form.input name="codigo_comision" label="Código de Comisión" placeholder="Ej: A" maxlength="20" :requerido="true" />
                            </div>
                            <div class="col-12 col-md-4">
                                <x-form.select name="modalidad_id" label="Modalidad" :opciones="$modalidades"
                                               placeholder="Seleccione una modalidad" :requerido="true" />
                            </div>
                            <div class="col-12 col-md-4">
                                <x-form.input type="number" name="capacidad_comision" label="Capacidad" placeholder="40" min="1" step="1" :requerido="true" />
                            </div>
                            <div class="col-12 col-md-3">
                                <x-form.select name="anio_curso" label="Año de Curso" :opciones="$anios" placeholder="Seleccione un año"
                                               :requerido="true" data-comision-anio data-cargas="{{ json_encode((object) $cargas) }}" />
                            </div>
                            <div class="col-12 col-md-3">
                                <x-form.input type="number" name="anio_lectivo" label="Año Lectivo" :value="now()->format('Y')" step="1" :requerido="true" />
                            </div>
                            <div class="col-12 col-md-3">
                                <x-form.input name="carga_horaria" label="Carga Horaria (calculada)" readonly data-comision-carga
                                              :value="$anioElegido ? ($cargas[$anioElegido] ?? 0).' hs' : ''" />
                            </div>
                            <div class="col-12 col-md-3">
                                <x-form.input name="horario_comision" label="Horario" placeholder="Ej: Lu-Mi-Vi 8 a 12 hs" maxlength="100" :requerido="true" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 px-0 d-flex flex-wrap justify-content-end">
                <button type="submit" class="btn btn-las las-btn-principal mr-2 mb-2">Registrar Comisión</button>
                <a href="{{ route('panel.comisiones.index', ['carrera' => $carrera->id]) }}" class="btn btn-outline-las las-btn-principal mb-2">Cancelar</a>
            </div>
        </div>
    </form>

    {{-- CU 17 alt. 10, 13, 16 y 19 (orden de la especificación): Aceptar → vuelve al campo. --}}
    <x-modal-aviso-errores :campos="[
        'anio_curso' => 'Año de Curso inválido',
        'anio_lectivo' => 'Año Lectivo inválido',
        'codigo_comision' => 'Código de Comisión existente',
        'capacidad_comision' => 'Capacidad inválida',
    ]" :mensajes="[
        'anio_curso' => \App\Services\Academico\ComisionService::MENSAJE_ANIO_CURSO,
        'anio_lectivo' => \App\Services\Academico\ComisionService::MENSAJE_ANIO_LECTIVO,
        'codigo_comision' => \App\Services\Academico\ComisionService::MENSAJE_CODIGO_EXISTENTE,
        'capacidad_comision' => \App\Services\Academico\ComisionService::MENSAJE_CAPACIDAD,
    ]" />
@endsection
