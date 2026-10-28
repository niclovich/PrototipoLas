{{-- Detalle de un registro de Auditoría: quién, cuándo, qué y los valores anteriores y nuevos. --}}
@extends('layouts.panel')

@section('title', 'Auditoría')

@section('encabezado')
    <x-header-with-title titulo="Auditoría" />
@endsection

@section('contenido')
    <div class="card las-tarjeta mb-4">
        <div class="card-body">
            <h3 class="las-subtitulo las-subtitulo--tarjeta">Registro #{{ $actividad->id }}</h3>
            <dl class="row mb-0">
                <dt class="col-sm-3">Fecha</dt><dd class="col-sm-9">{{ $actividad->created_at?->format('d/m/Y H:i:s') }}</dd>
                <dt class="col-sm-3">Usuario</dt><dd class="col-sm-9">{{ $actividad->causer?->nombre_completo ?? 'Sistema' }}</dd>
                <dt class="col-sm-3">Módulo</dt><dd class="col-sm-9">{{ $actividad->log_name }}</dd>
                <dt class="col-sm-3">Evento</dt><dd class="col-sm-9">{{ \App\Support\EventoAuditoria::etiqueta($actividad->event ?? $actividad->description) }}</dd>
                <dt class="col-sm-3">Registro</dt><dd class="col-sm-9">{{ class_basename((string) $actividad->subject_type) }} #{{ $actividad->subject_id ?? '—' }}</dd>
            </dl>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table mb-4">
            <thead class="las-thead">
                <tr><th scope="col">Campo</th><th scope="col">Valor anterior</th><th scope="col">Valor nuevo</th></tr>
            </thead>
            <tbody>
                @forelse ($cambios as $cambio)
                    <tr><td>{{ $cambio['campo'] }}</td><td>{{ $cambio['anterior'] }}</td><td>{{ $cambio['nuevo'] }}</td></tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted">El registro no tiene cambios de atributos.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <a href="{{ route('panel.auditoria.index') }}" class="btn btn-outline-las las-btn-principal">Volver</a>
@endsection
