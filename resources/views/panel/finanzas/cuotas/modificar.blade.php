{{-- CU 34: P2 Formulario Modificar Cuota (wireframe Modificar_P2.png, Fig. 248). Solo cambia el monto. --}}
@extends('layouts.panel')

@section('title', 'Modificar Cuota')

@section('encabezado')
    <x-header-with-title titulo="GESTIÓN DE CUOTAS" />
@endsection

@section('contenido')
    <form action="{{ route('panel.cuotas.update', $cuota) }}" method="POST" novalidate>
        @csrf
        @method('PUT')
        <div class="card card-las-plano">
            <div class="card-body">
                <h2 class="las-titulo-pantalla">Modificar Cuota</h2>

                <div class="card las-tarjeta">
                    <div class="card-body">
                        <h3 class="las-subtitulo las-subtitulo--tarjeta">Datos de la Cuota</h3>
                        <div class="row">
                            <div class="col-12 col-lg-8">
                                <x-form.input name="carrera_nombre" label="Carrera" :value="$cuota->carrera->nombre_carrera" readonly />
                            </div>
                            <div class="w-100"></div>
                            <div class="col-12 col-md-3 col-lg-2">
                                <x-form.input name="anio_lectivo" label="Año Lectivo" :value="$cuota->anio" readonly />
                            </div>
                            <div class="col-12 col-md-3">
                                <x-form.input name="mes" label="Mes" :value="$cuota->mes->descripcion" readonly />
                            </div>
                            <div class="col-12 col-md-3">
                                <x-form.input name="monto_actual" label="Monto Actual" :value="\App\Support\Moneda::formatear($cuota->monto)" readonly />
                            </div>
                            <div class="col-12 col-md-3 col-lg-4">
                                <x-form.input name="monto" label="Nuevo Monto" placeholder="Ej: 50000" inputmode="decimal" :requerido="true" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 px-0 d-flex flex-wrap justify-content-end">
                <a href="{{ route('panel.cuotas.index', ['carrera' => $cuota->carrera_id, 'anio' => $cuota->anio]) }}" class="btn btn-outline-las las-btn-principal mr-2 mb-2">Cancelar</a>
                <button type="submit" class="btn btn-las las-btn-principal mb-2">Guardar Cambios</button>
            </div>
        </div>
    </form>

    {{-- CU 34 alt. 15: Aceptar → vuelve al campo. --}}
    <x-modal-aviso-errores :campos="['monto' => 'Monto inválido']" />
@endsection
