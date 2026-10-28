{{-- CU4 P3 Alumnos Inscriptos a la Materia (CU4_regularidad/wireframes/P3.png, Fig. 78): pasos 11–13. "En Curso" solo se muestra. --}}
@extends('layouts.panel')

@section('title', 'Alumnos Inscriptos')

@section('encabezado')
    <x-header-with-title titulo="ALUMNOS INSCRIPTOS" />
@endsection

@php
    $nota = [\App\Services\Alumnos\RegularidadService::class, 'formatearNota'];
    $fueraDeAlcance = 'Fuera del alcance del prototipo';
@endphp

@section('contenido')
    <div class="card las-tarjeta mb-3">
        <div class="card-body py-4">
            <strong>Materia: {{ $det->detallePlan->materia->nombre_materia }} ({{ $det->detallePlan->codigo_det_plan_estudio }}) · Comision: {{ $det->comision->codigo_comision }}</strong>
        </div>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-end mb-3">
        <div class="mb-2">
            <label for="filtro-alumnos" class="las-etiqueta-filtro">Filtrar</label>
            <input type="search" id="filtro-alumnos" class="form-control" placeholder="Buscar.." aria-label="Buscar por nombre, legajo o DNI"
                   autocomplete="off" data-filtro-tabla="#tabla-inscriptos">
        </div>
        <div class="mb-2">
            @foreach (['Registrar Alumno', 'Reporte'] as $texto)
                <span class="d-inline-block ml-2" tabindex="0" data-toggle="tooltip" title="{{ $fueraDeAlcance }}">
                    <button type="button" class="btn btn-las las-btn-principal las-btn-deshabilitado" disabled>{{ $texto }}</button>
                </span>
            @endforeach
        </div>
    </div>

    <div class="table-responsive">
        <table class="table" id="tabla-inscriptos">
            <thead class="las-thead">
                <tr>
                    <th scope="col">Legajo</th>
                    <th scope="col">Alumno</th>
                    <th scope="col">Nota P1</th>
                    <th scope="col">Nota P2</th>
                    <th scope="col">Nota R1</th>
                    <th scope="col">Nota R2</th>
                    <th scope="col">Faltas</th>
                    <th scope="col">Condicion Final</th>
                    <th scope="col">Accion</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($inscriptos as $inscripcion)
                    <tr data-fila>
                        <td>{{ $inscripcion->alumno->nro_legajo ?? '-' }}<span class="d-none">{{ $inscripcion->alumno->usuario->dni_usuario }}</span></td>
                        <td>{{ $inscripcion->alumno->usuario->nombre_completo }}</td>
                        @foreach (array_keys(\App\Services\Alumnos\RegularidadService::NOTAS) as $campo)
                            <td>{{ $nota($inscripcion->{$campo}) }}</td>
                        @endforeach
                        <td>{{ $inscripcion->faltas_alumno ?? 0 }}</td>
                        <td>{{ \App\Enums\CondicionCursada::etiqueta($inscripcion->condicion_final) }}</td>
                        <td class="text-nowrap">
                            <x-btn-accion :href="route('panel.regularidad.edit', $inscripcion)" icono="fas fa-pencil-alt" texto="Registrar regularidad" color="las" />
                            <x-btn-accion icono="fas fa-trash-alt" texto="Eliminar" color="las" :deshabilitado="$fueraDeAlcance" />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted">No hay alumnos inscriptos en la materia.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-end mt-4">
        <a href="{{ route('panel.regularidad.materias', $det->comision_id) }}" class="btn btn-outline-las las-btn-principal">Volver</a>
    </div>
@endsection
