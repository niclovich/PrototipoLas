{{--
    CU 8: P2 Formulario Registrar Correlatividades (Correlatividades_P2.png). "+ Agregar" suma la fila a la tabla y 🗑 la quita
    (resources/js/plan-estudio.js); "Registrar Correlatividades" guarda la tabla completa.
    Variables: $detalle, $disponibles [id => [materia, codigo]], $filas (correlativas actuales u old()), $condiciones [valor => [opcion, celda]].
--}}
@extends('layouts.panel')

@section('title', 'Registrar Correlatividades de Materia')

@section('encabezado')
    <x-header-with-title titulo="GESTIÓN DE PLAN DE ESTUDIO" />
@endsection

@section('contenido')
    <form action="{{ route('panel.planes.correlatividades.store', $detalle->id) }}" method="POST" novalidate data-correlatividades
          data-disponibles="{{ json_encode((object) $disponibles) }}" data-condiciones="{{ json_encode($condiciones) }}">
        @csrf
        <div class="card card-las-plano">
            <div class="card-body">
                <h2 class="las-titulo-pantalla">Registrar Correlatividades de Materia</h2>

                <div class="card las-tarjeta">
                    <div class="card-body">
                        <h3 class="las-subtitulo las-subtitulo--tarjeta">Datos de la Materia</h3>
                        <div class="row">
                            <div class="col-12 col-md-4"><x-form.input name="dato_carrera" label="Carrera" :value="$detalle->planEstudio->carrera->nombre_carrera" readonly /></div>
                            <div class="col-12 col-sm-4 col-md-2"><x-form.input name="dato_plan" label="Año del Plan" :value="$detalle->planEstudio->anio_plan_estudio" readonly /></div>
                            <div class="col-12 col-sm-8 col-md-4"><x-form.input name="dato_materia" label="Materia" :value="$detalle->materia->nombre_materia" readonly /></div>
                            <div class="col-12 col-md-2"><x-form.input name="dato_codigo" label="Código" :value="$detalle->codigo_det_plan_estudio" readonly /></div>
                        </div>
                    </div>
                </div>

                <div class="card las-tarjeta">
                    <div class="card-body">
                        <h3 class="las-subtitulo las-subtitulo--tarjeta">1 · Agregar Correlativa</h3>
                        <div class="row align-items-end">
                            <div class="col-12 col-md-4">
                                <x-form.select name="nueva_correlativa" label="Materia Correlativa" placeholder="Seleccione una materia del plan"
                                               :opciones="collect($disponibles)->map(fn ($d) => $d['materia'])->all()" data-nueva="correlativa" form="sin-envio" />
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <x-form.select name="nueva_p_cursar" label="Para Cursar" placeholder="Seleccione..." :value="\App\Enums\CondicionCorrelativa::Regular->value"
                                               :opciones="collect($condiciones)->map(fn ($c) => $c['opcion'])->all()" data-nueva="p_cursar" form="sin-envio" />
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <x-form.select name="nueva_p_rendir" label="Para Rendir" placeholder="Seleccione..." :value="\App\Enums\CondicionCorrelativa::Aprobada->value"
                                               :opciones="collect($condiciones)->map(fn ($c) => $c['opcion'])->all()" data-nueva="p_rendir" form="sin-envio" />
                            </div>
                            <div class="col-12 col-md-2 form-group">
                                <button type="button" class="btn btn-outline-las btn-block" data-agregar-correlativa>+ Agregar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="text-muted text-uppercase mb-2">Correlativas de la Materia</p>
                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
                @endif
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="tabla-correlativas">
                        <thead class="las-thead">
                            <tr>
                                <th scope="col">Materia Correlativa</th>
                                <th scope="col">Código</th>
                                <th scope="col">Para Cursar</th>
                                <th scope="col">Para Rendir</th>
                                <th scope="col">Acción</th>
                            </tr>
                        </thead>
                        <tbody data-filas-correlativas>
                            @foreach (array_values($filas) as $i => $fila)
                                @php $dato = $disponibles[$fila['detalle_plan_correlativa_id'] ?? 0] ?? null; @endphp
                                @continue(! $dato)
                                <tr data-correlativa="{{ $fila['detalle_plan_correlativa_id'] }}">
                                    <td>{{ $dato['materia'] }}</td>
                                    <td>{{ $dato['codigo'] }}</td>
                                    <td>{{ $condiciones[$fila['p_cursar']]['celda'] ?? '' }}</td>
                                    <td>{{ $condiciones[$fila['p_rendir']]['celda'] ?? '' }}</td>
                                    <td>
                                        <input type="hidden" name="correlativas[{{ $i }}][detalle_plan_correlativa_id]" value="{{ $fila['detalle_plan_correlativa_id'] }}">
                                        <input type="hidden" name="correlativas[{{ $i }}][p_cursar]" value="{{ $fila['p_cursar'] }}">
                                        <input type="hidden" name="correlativas[{{ $i }}][p_rendir]" value="{{ $fila['p_rendir'] }}">
                                        <button type="button" class="btn btn-sm btn-las" data-quitar-correlativa title="Quitar" aria-label="Quitar correlativa">
                                            <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 px-0 d-flex flex-wrap justify-content-end">
                <a href="{{ route('panel.planes.index', ['carrera' => $detalle->planEstudio->carrera_id]) }}" class="btn btn-outline-las las-btn-principal mr-2 mb-2">Cancelar</a>
                <button type="submit" class="btn btn-las las-btn-principal mb-2">Registrar Correlatividades</button>
            </div>
        </div>
    </form>

    <x-modal-aviso id="modal-correlativa" titulo="Correlativa" />
@endsection
