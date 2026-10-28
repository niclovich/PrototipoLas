{{--
    P2 Registrar Carrera (Registrar_P2.png) y P2 Modificar Carrera (Modificar_P2.png).
    Variables: $carrera, $modalidades, $duraciones, $estados, $modo ('registrar' | 'modificar'), $soloEstado,
    $accion, $metodo, $titulo, $boton. Con Plan de Estudio activo ($soloEstado) solo se habilita el Estado (CU 4 paso 5).
--}}
@php
    $modificar = $modo === 'modificar';
    $aviso = $soloEstado ? \App\Services\Academico\CarreraService::AVISO_PLAN_ACTIVO : null;
@endphp
<form action="{{ $accion }}" method="POST" novalidate>
    @csrf
    @if ($metodo !== 'POST')
        @method($metodo)
    @endif
    <div class="card card-las-plano">
        <div class="card-body">
            <h2 class="las-titulo-pantalla">{{ $titulo }}</h2>

            <div class="card las-tarjeta">
                <div class="card-body">
                    <h3 class="las-subtitulo las-subtitulo--tarjeta">Datos de la Carrera</h3>
                    <div class="row justify-content-center">
                        <div class="col-12 col-lg-10">
                            <div class="row">
                                <div class="col-12">
                                    <x-form.input name="nombre_carrera" label="Nombre de la Carrera" :value="$carrera->nombre_carrera" :requerido="true"
                                                  placeholder="Ingrese el nombre de la carrera" maxlength="150"
                                                  :disabled="$soloEstado" :title="$aviso" />
                                </div>
                                <div class="col-12 col-md-6">
                                    <x-form.select name="modalidad_id" label="Modalidad" :opciones="$modalidades" :value="$carrera->modalidad_id"
                                                   placeholder="Seleccione una modalidad" :requerido="true" :disabled="$soloEstado" :title="$aviso" />
                                </div>
                                <div class="col-12 col-md-6">
                                    <x-form.select name="duracion_id" label="Duración" :opciones="$duraciones" :value="$carrera->duracion_id"
                                                   placeholder="Seleccione una duración" :requerido="true" :disabled="$soloEstado" :title="$aviso" />
                                </div>
                                <div class="{{ $modificar ? 'col-12 col-md-6' : 'col-12' }}">
                                    <x-form.input name="resolucion_carrera" label="Resolución Ministerial" :requerido="true" maxlength="50"
                                                  :value="$carrera->exists ? $carrera->resolucion_texto : null"
                                                  placeholder="Ej: Resolución N° 1234/2020" :disabled="$soloEstado" :title="$aviso" />
                                </div>
                                @if ($modificar)
                                    <div class="col-12 col-md-6">
                                        <x-form.select name="estado_carrera" label="Estado" :opciones="$estados" :value="$carrera->estado_carrera"
                                                       placeholder="Seleccione un estado" :requerido="true" />
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer bg-transparent border-0 px-0 d-flex flex-wrap justify-content-end">
            <a href="{{ route('panel.carreras.index') }}" class="btn btn-outline-las las-btn-principal mr-2 mb-2">Cancelar</a>
            <button type="submit" class="btn btn-las las-btn-principal mb-2">{{ $boton }}</button>
        </div>
    </div>
</form>

{{-- Alternativos: CU 3 alt. 4 / 13 y CU 4 alt. 8 / 9 (primero el nombre, luego la resolución). --}}
<x-modal-aviso-errores :campos="['nombre_carrera' => 'Carrera ya existente', 'resolucion_carrera' => 'Resolución inválida']" />
