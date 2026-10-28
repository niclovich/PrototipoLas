{{-- CU3 P2 Historial de comisiones del Alumno (CU3 P2.png, Fig. 73; con "Cambiar" de la Fig. 230): pasos 6–8. --}}
@extends('layouts.panel')

@section('title', 'Alumno Comisiones')

@section('encabezado')
    <x-header-with-title :titulo="\App\Http\Controllers\Panel\Alumnos\InscripcionComisionController::ENCABEZADO" />
@endsection

@section('contenido')
    @include('panel.alumnos._datos-alumno')

    <h3 class="las-subtitulo text-dark font-weight-normal">HISTORIAL DE COMISIONES</h3>
    <div class="table-responsive">
        <table class="table mb-4">
            <thead class="las-thead">
                <tr>
                    <th scope="col">Anio Curso</th>
                    <th scope="col">Comision</th>
                    <th scope="col">Modalidad</th>
                    <th scope="col">Horario</th>
                    <th scope="col">Anio Lectivo</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($historial as $comision)
                    <tr>
                        <td>{{ $comision->anio_curso }}</td>
                        <td>{{ $comision->codigo_comision }}</td>
                        <td>{{ $comision->modalidad?->modalidad }}</td>
                        <td>{{ $comision->horario_comision }}</td>
                        <td>{{ $comision->anio_lectivo }}</td>
                        <td>
                            @if ($comision->id === $cambiable)
                                @can('editar_inscripcion_comision')
                                    <a href="{{ route('panel.alumno-comisiones.cambio', $alumno) }}" class="btn btn-sm btn-las px-4">Cambiar</a>
                                @endcan
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">El alumno no tiene comisiones registradas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex flex-wrap justify-content-between mt-4">
        <a href="{{ route('panel.alumnos.index') }}" class="btn btn-outline-las las-btn-principal mb-2">Volver</a>
        <a href="{{ route('panel.alumno-comisiones.create', $alumno) }}" class="btn btn-las las-btn-principal mb-2">Nueva Comision</a>
    </div>

    <x-modal-aviso id="modal-aviso-comisiones" titulo="Cambio de Comisión"
                   :mensaje="session(\App\Http\Controllers\Panel\Alumnos\InscripcionComisionController::AVISO)"
                   :abrir="session()->has(\App\Http\Controllers\Panel\Alumnos\InscripcionComisionController::AVISO)" />
@endsection
