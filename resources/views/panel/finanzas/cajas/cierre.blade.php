{{--
    CU 36 P2 Registrar Cierre de Caja (soporte/caja/wireframes/Cierre_P2.png, Fig. 253), desde "Ver" de P1.
    Caja Cerrada o ajena: mismos datos en solo lectura y "Confirmar Cierre" deshabilitado (E13-D2). Alt. 8.1 y 14.1 en modal.
--}}
@extends('layouts.panel')

@section('title', 'Registrar Cierre de Caja')

@section('encabezado')
    <x-header-with-title :titulo="\App\Http\Controllers\Panel\Finanzas\CajaController::ENCABEZADO" />
@endsection

@use('App\Services\Finanzas\CajaService')
@use('App\Support\Moneda')
@php
    $errorContado = collect($errors->getMessages())->keys()->contains(fn ($k) => str_starts_with($k, 'contados'));
@endphp

@section('contenido')
    <form action="{{ route('panel.cajas.cerrar', $caja) }}" method="POST" novalidate data-cierre-caja
          @if ($editable) data-confirm="¿Confirma el cierre de la caja? Una vez cerrada no admite nuevos pagos." @endif>
        @csrf
        <div class="card card-las-plano">
            <div class="card-body">
                <h2 class="las-titulo-pantalla">Registrar Cierre de Caja</h2>

                <div class="card las-tarjeta">
                    <div class="card-body">
                        <h3 class="las-subtitulo las-subtitulo--tarjeta mt-0">Datos de la Caja</h3>
                        <div class="row">
                            <div class="col-12 col-md-2"><x-form.input name="caja" label="Caja" :value="$caja->id" readonly tabindex="-1" /></div>
                            <div class="col-12 col-md-3"><x-form.input name="cajero" label="Cajero" :value="$caja->cajero?->name" readonly tabindex="-1" /></div>
                            <div class="col-12 col-md-4"><x-form.input name="fecha_apertura" label="Fecha de Apertura" :value="$caja->fecha_apertura->format('d/m/Y H:i:s')" readonly tabindex="-1" /></div>
                            <div class="col-12 col-md-3"><x-form.input name="monto_inicial" label="Monto Inicial" :value="Moneda::formatear($caja->monto_inicial)" readonly tabindex="-1" /></div>
                        </div>
                    </div>
                </div>

                <div class="card las-tarjeta">
                    <div class="card-body">
                        <h3 class="las-subtitulo las-subtitulo--tarjeta mt-0">Detalle de Cierre por Medio de Pago</h3>
                        <div class="table-responsive">
                            <table class="table mb-2">
                                <thead class="las-thead">
                                    <tr><th scope="col">MEDIO DE PAGO</th><th scope="col">MONTO ESPERADO</th><th scope="col">MONTO CONTADO</th><th scope="col">DIFERENCIA</th></tr>
                                </thead>
                                <tbody>
                                    @foreach ($filas as $fila)
                                        @php $id = $fila['medio']->id; @endphp
                                        <tr data-fila-medio data-esperado="{{ $fila['esperado'] }}">
                                            <td>{{ $fila['medio']->medio_pago }}</td>
                                            <td>{{ Moneda::formatear($fila['esperado']) }}</td>
                                            <td style="min-width: 10rem;">
                                                <label for="contado_{{ $id }}" class="sr-only">Monto contado en {{ $fila['medio']->medio_pago }}</label>
                                                <input type="text" name="contados[{{ $id }}]" id="contado_{{ $id }}" inputmode="decimal" data-contado
                                                       value="{{ old("contados.{$id}", $fila['contado'] === null ? '' : $fila['contado']) }}"
                                                       @class(['form-control las-select-bordo', 'is-invalid' => $errors->has("contados.{$id}")]) @readonly(! $editable)>
                                            </td>
                                            <td data-diferencia>{{ $fila['diferencia'] === null ? '—' : Moneda::formatear($fila['diferencia']) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-end align-items-center">
                            <label for="diferencia_total" class="las-etiqueta-filtro mb-0 mr-3">Diferencia Total</label>
                            <input type="text" id="diferencia_total" class="form-control" style="max-width: 12rem;" readonly tabindex="-1" data-diferencia-total
                                   value="{{ Moneda::formatear($caja->diferencia_total ?? 0) }}">
                        </div>
                    </div>
                </div>

                <div class="card las-tarjeta">
                    <div class="card-body">
                        <h3 class="las-subtitulo las-subtitulo--tarjeta mt-0"><label for="observacion_cierre" class="mb-0">Observación del Cierre</label></h3>
                        <textarea name="observacion_cierre" id="observacion_cierre" rows="3" maxlength="1000" @readonly(! $editable)
                                  @class(['form-control', 'is-invalid' => $errors->has('observacion_cierre')])>{{ old('observacion_cierre', $caja->observacion_cierre) }}</textarea>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 px-0 d-flex flex-wrap justify-content-end">
                <a href="{{ route('panel.cajas.index') }}" class="btn btn-outline-las las-btn-principal mr-2 mb-2">Cancelar</a>
                @if ($editable)
                    <button type="submit" class="btn btn-las las-btn-principal mb-2">Confirmar Cierre</button>
                @else
                    <span class="d-inline-block mb-2" tabindex="0" data-toggle="tooltip" title="La caja no está abierta para el cajero">
                        <button type="button" class="btn btn-las las-btn-principal las-btn-deshabilitado" disabled>Confirmar Cierre</button>
                    </span>
                @endif
            </div>
        </div>
    </form>

    <x-modal-aviso id="modal-monto-contado" titulo="Monto inválido" :mensaje="CajaService::MENSAJE_MONTO_CONTADO" :abrir="$errorContado" />
    <x-modal-aviso id="modal-observacion" titulo="Observación faltante" :mensaje="CajaService::MENSAJE_OBSERVACION"
                   :abrir="! $errorContado && $errors->has('observacion_cierre')" foco="#observacion_cierre" />
@endsection
