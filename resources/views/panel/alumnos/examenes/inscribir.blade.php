{{-- CU5 P2 Actas de Examen Disponibles (CU5 P2.png, Fig. 81): pasos 4–12. "Dar de Baja" fuera de alcance (§7-10). --}}
@extends('layouts.panel')

@section('title', 'Inscripción a Exámenes')

@section('encabezado')
    <x-header-with-title :titulo="\App\Http\Controllers\Panel\Alumnos\InscripcionExamenController::ENCABEZADO" />
@endsection

@section('contenido')
    @include('panel.alumnos._datos-alumno')

    <form method="GET" action="{{ route('panel.inscripciones-examen.create', $alumno) }}" class="row justify-content-center mb-3">
        <div class="col-12 col-md-8 col-lg-6 form-group">
            <label for="materia" class="text-muted">Seleccionar Materia</label>
            <select name="materia" id="materia" class="form-control las-select-bordo" data-autoenviar>
                <option value="">Seleccione una materia</option>
                @foreach ($materias as $materia)
                    <option value="{{ $materia->id }}" @selected($seleccionada?->id === $materia->id)>{{ $materia->materia->nombre_materia }}</option>
                @endforeach
            </select>
            <noscript><button type="submit" class="btn btn-las mt-2">Ver actas</button></noscript>
        </div>
    </form>

    <h3 class="las-subtitulo text-muted font-weight-normal">ACTAS DE EXAMEN DISPONIBLES PARA LA MATERIA SELECCIONADA</h3>
    <div class="table-responsive">
        <table class="table mb-4">
            <thead class="las-thead">
                <tr>
                    <th scope="col">Nro Acta</th>
                    <th scope="col">Modalidad</th>
                    <th scope="col">Llamado</th>
                    <th scope="col">Tipo Acta</th>
                    <th scope="col">Fecha</th>
                    <th scope="col">Accion</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($actas as $acta)
                    @php $inscripcion = $acta->inscriptos->first(); @endphp
                    <tr>
                        <td>{{ $acta->nro_acta_texto }}</td>
                        <td>{{ $acta->modalidad->modalidad }}</td>
                        <td>{{ $acta->llamado->llamado }}</td>
                        <td>{{ $acta->tipoActa->descripcion }}</td>
                        <td>{{ $acta->fecha_acta->format('d/m/Y') }}</td>
                        <td class="text-nowrap">
                            @if ($inscripcion)
                                <span class="d-inline-block" tabindex="0" data-toggle="tooltip" title="Fuera del alcance del prototipo">
                                    <button type="button" class="btn btn-sm btn-outline-las px-4 las-btn-deshabilitado" disabled>Dar de Baja</button>
                                </span>
                                @can('exportar_inscripcion_examen')
                                    <a href="{{ route('panel.inscripciones-examen.comprobante', $inscripcion) }}" class="btn btn-sm btn-las"
                                       data-toggle="tooltip" title="Descargar comprobante de inscripción">Compr.</a>
                                @endcan
                            @else
                                <form action="{{ route('panel.inscripciones-examen.store', $alumno) }}" method="POST" class="d-inline"
                                      data-confirm="¿Confirma la inscripción al acta {{ $acta->nro_acta_texto }} ({{ $acta->llamado->llamado }}, {{ $acta->fecha_acta->format('d/m/Y') }})?">
                                    @csrf
                                    <input type="hidden" name="acta_id" value="{{ $acta->id }}">
                                    <input type="hidden" name="materia" value="{{ $seleccionada?->id }}">
                                    <button type="submit" class="btn btn-sm btn-las px-4">Inscribir</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            {{ $seleccionada ? 'No hay actas de examen pendientes para la materia seleccionada.' : 'Seleccione una materia para ver sus actas de examen.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <a href="{{ route('panel.alumnos.index') }}" class="btn btn-outline-las las-btn-principal">Volver</a>

    <x-modal-aviso id="modal-aviso-examenes" :titulo="\App\Http\Controllers\Panel\Alumnos\InscripcionExamenController::TITULO"
                   :mensaje="session(\App\Http\Controllers\Panel\Alumnos\InscripcionExamenController::AVISO)"
                   :abrir="session()->has(\App\Http\Controllers\Panel\Alumnos\InscripcionExamenController::AVISO)" foco="#materia" />
@endsection
