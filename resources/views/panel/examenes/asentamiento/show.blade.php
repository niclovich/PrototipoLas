{{--
    CU6 P2 Alumnos Inscriptos al Acta (CU6 P2.png, Fig. 84): pasos 4–6 y 14–19.
    "Reporte Listado Acta" y 🗑 fuera de alcance (deshabilitados). Nro de Folio y "Asentar Acta" se habilitan con todas las notas.
--}}
@extends('layouts.panel')

@section('title', 'Asentamiento de Acta')

@section('encabezado')
    <x-header-with-title :titulo="\App\Http\Controllers\Panel\Examenes\AsentamientoActaController::ENCABEZADO" />
@endsection

@php
    $fueraDeAlcance = 'Fuera del alcance del prototipo';
    $titulo = \App\Http\Controllers\Panel\Examenes\AsentamientoActaController::TITULO;
    $aviso = \App\Http\Controllers\Panel\Examenes\AsentamientoActaController::AVISO;
@endphp

@section('contenido')
    <div class="card las-tarjeta mb-4">
        <div class="card-body">
            <h3 class="las-subtitulo las-subtitulo--tarjeta">Datos del Acta</h3>
            <div class="row">
                <div class="col-12 col-md-2">
                    <x-form.input name="nro_acta" label="Nro Acta" :value="$acta->nro_acta_texto" readonly />
                </div>
                <div class="col-12 col-md-4">
                    <x-form.input name="materia" label="Materia" :value="$acta->detallePlan->materia->nombre_materia" readonly />
                </div>
                <div class="col-12 col-md-3">
                    <x-form.input name="modalidad_llamado" label="Modalidad · Llamado" :value="$acta->modalidad->modalidad.' · '.$acta->llamado->llamado" readonly />
                </div>
                <div class="col-12 col-md-3">
                    <x-form.input name="fecha" label="Fecha" :value="$acta->fecha_acta->format('d/m/Y')" readonly />
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <h3 class="las-subtitulo font-weight-normal mb-2">ALUMNOS INSCRIPTOS AL ACTA</h3>
        <span class="d-inline-block mb-2" tabindex="0" data-toggle="tooltip" title="{{ $fueraDeAlcance }}">
            <button type="button" class="btn btn-las las-btn-principal las-btn-deshabilitado" disabled>Reporte Listado Acta</button>
        </span>
    </div>

    <div class="table-responsive">
        <table class="table mb-4">
            <thead class="las-thead">
                <tr>
                    <th scope="col">Legajo</th>
                    <th scope="col">Alumno</th>
                    <th scope="col">Nota</th>
                    <th scope="col">Observacion</th>
                    <th scope="col">Condicion Final</th>
                    <th scope="col">Accion</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($inscriptos as $detalle)
                    <tr>
                        <td>{{ $detalle->alumno->nro_legajo }}</td>
                        <td>{{ $detalle->alumno->usuario->nombre_completo }}</td>
                        <td>{{ \App\Services\Alumnos\RegularidadService::formatearNota($detalle->nota_acta) }}</td>
                        <td>{{ $detalle->observacion ?: '-' }}</td>
                        <td>{{ $detalle->condicion_final?->value ?? \App\Services\Examenes\AsentamientoActaService::SIN_REGISTRAR }}</td>
                        <td class="text-nowrap">
                            <x-btn-accion :href="route('panel.asentamiento.edit', $detalle)" icono="fas fa-pen" texto="Registrar nota" color="las" />
                            <x-btn-accion icono="fas fa-trash-alt" texto="Eliminar" color="las" :deshabilitado="$fueraDeAlcance" />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">El acta no tiene alumnos inscriptos.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <form action="{{ route('panel.asentamiento.store', $acta) }}" method="POST" novalidate
          data-confirm="Se asentará el acta {{ $acta->nro_acta_texto }} con las notas cargadas y el número de folio ingresado. Esta acción no se puede deshacer.">
        @csrf
        <div class="card las-tarjeta mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-12 col-md-4">
                        <x-form.input name="nro_folio" label="Nro de Folio" inputmode="numeric" :disabled="! $completa" />
                    </div>
                    <div class="col-12 col-md-8">
                        <p class="text-muted font-italic small mb-0">Se habilita una vez que todos los alumnos tienen su nota, observacion y estado registrados.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap justify-content-between mt-4">
            <a href="{{ route('panel.actas.index', ['carrera' => $acta->detallePlan->planEstudio->carrera_id]) }}" class="btn btn-outline-las las-btn-principal mb-2">Volver</a>
            <button type="submit" class="btn btn-las las-btn-principal mb-2" @disabled(! $completa)>Asentar Acta</button>
        </div>
    </form>

    {{-- Alt. 16.1: "El número de folio es inválido" → Aceptar → vuelve al paso 14. --}}
    <x-modal-aviso-errores :campos="['nro_folio' => $titulo]" />
    <x-modal-aviso id="modal-aviso-asentamiento" :titulo="$titulo" :mensaje="session($aviso)" :abrir="session()->has($aviso)" />
@endsection
