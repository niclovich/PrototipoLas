{{-- CU 35 P2 Registrar Apertura de Caja (soporte/caja/wireframes/Apertura_P2.png, Fig. 251). Alt. 5.1 en modal. --}}
@extends('layouts.panel')

@section('title', 'Registrar Apertura de Caja')

@section('encabezado')
    <x-header-with-title :titulo="\App\Http\Controllers\Panel\Finanzas\CajaController::ENCABEZADO" />
@endsection

@section('contenido')
    <form action="{{ route('panel.cajas.store') }}" method="POST" novalidate>
        @csrf
        <div class="card card-las-plano">
            <div class="card-body">
                <h2 class="las-titulo-pantalla">Registrar Apertura de Caja</h2>
                <div class="card las-tarjeta">
                    <div class="card-body">
                        <h3 class="las-subtitulo las-subtitulo--tarjeta mt-0">Datos de la Caja</h3>
                        <div class="row">
                            <div class="col-12 col-md-5">
                                <x-form.input name="fecha_apertura" label="Fecha de Apertura" :value="$fechaApertura->format('d/m/Y H:i:s')" readonly tabindex="-1" />
                            </div>
                            <div class="col-12 col-md-5">
                                <x-form.input name="monto_inicial" label="Monto Inicial" placeholder="Ej: 5000" inputmode="decimal" :requerido="true" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 px-0 d-flex flex-wrap justify-content-end">
                <a href="{{ route('panel.cajas.index') }}" class="btn btn-outline-las las-btn-principal mr-2 mb-2">Cancelar</a>
                <button type="submit" class="btn btn-las las-btn-principal mb-2">Registrar Apertura</button>
            </div>
        </div>
    </form>

    <x-modal-aviso id="modal-monto-inicial" titulo="Monto inválido" :mensaje="\App\Services\Finanzas\CajaService::MENSAJE_MONTO_INICIAL"
                   :abrir="$errors->has('monto_inicial')" foco="#monto_inicial" />
@endsection
