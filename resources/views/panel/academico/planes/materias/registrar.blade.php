{{-- CU 6: P2 Formulario Registrar Materia al Plan (Registrar_Materia_P2.png). Se agrega al plan "Activo" de la carrera. --}}
@extends('layouts.panel')

@section('title', 'Registrar Materia al Plan')

@section('encabezado')
    <x-header-with-title titulo="GESTIÓN DE PLAN DE ESTUDIO" />
@endsection

@section('contenido')
    <form action="{{ route('panel.planes.materias.store', $plan) }}" method="POST" novalidate>
        @csrf
        <div class="card card-las-plano">
            <div class="card-body">
                <h2 class="las-titulo-pantalla">Registrar Materia al Plan</h2>

                <div class="card las-tarjeta">
                    <div class="card-body">
                        <h3 class="las-subtitulo las-subtitulo--tarjeta">1 · Datos de la Materia</h3>
                        <div class="row">
                            <div class="col-12">
                                <x-form.input name="carrera_nombre" label="Carrera" :value="$plan->carrera->nombre_carrera" readonly />
                            </div>
                            <div class="col-12 col-md-5">
                                <x-form.select name="materia_id" label="Materia" :opciones="$materias" placeholder="Seleccione una materia activa"
                                               :requerido="true" :select2="true" />
                            </div>
                            <div class="col-12 col-md-4">
                                <x-form.input name="codigo_det_plan_estudio" label="Código de Materia" placeholder="Ej: AM2" maxlength="20" :requerido="true" />
                            </div>
                            <div class="col-12 col-md-3">
                                <x-form.select name="regimen_id" label="Régimen" :opciones="$regimenes" placeholder="Seleccione un régimen" :requerido="true" />
                            </div>
                            <div class="col-12 col-md-4">
                                <x-form.select name="cuatrimestre_id" label="Cuatrimestre" :opciones="$cuatrimestres" placeholder="Seleccione un cuatrimestre" :requerido="true" />
                            </div>
                            <div class="col-12 col-md-3">
                                <x-form.select name="anio_curso_det_plan_estudio" label="Año de Dictado" :opciones="$anios" placeholder="Seleccione un año" :requerido="true" />
                            </div>
                            <div class="col-12 col-md-3">
                                <x-form.input type="number" name="carga_horaria_det_plan_estudio" label="Carga Horaria" placeholder="96"
                                              min="1" max="{{ \App\Services\Academico\DetallePlanEstudioService::CARGA_MAXIMA }}" step="1" :requerido="true" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 px-0 d-flex flex-wrap justify-content-end">
                <a href="{{ route('panel.planes.index', ['carrera' => $plan->carrera_id]) }}" class="btn btn-outline-las las-btn-principal mr-2 mb-2">Cancelar</a>
                <button type="submit" class="btn btn-las las-btn-principal mb-2">Registrar Materia</button>
            </div>
        </div>
    </form>

    {{-- CU 6 alt. 8, 11 y 23 (en el orden de la especificación): Aceptar → vuelve al paso del campo. --}}
    <x-modal-aviso-errores :campos="[
        'materia_id' => 'Materia ya cargada',
        'codigo_det_plan_estudio' => 'Código existente',
        'carga_horaria_det_plan_estudio' => 'Carga horaria inválida',
    ]" :mensajes="[
        'materia_id' => \App\Services\Academico\DetallePlanEstudioService::MENSAJE_MATERIA_YA_CARGADA,
        'codigo_det_plan_estudio' => \App\Services\Academico\DetallePlanEstudioService::MENSAJE_CODIGO_EXISTENTE,
    ]" />
@endsection
