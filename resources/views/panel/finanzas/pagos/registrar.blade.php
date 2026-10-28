{{-- CU7 P2 Registrar Pago de Cuotas (CU7_pago_cuotas/wireframes/P2.png, Fig. 87): pasos 6–18. --}}
@extends('layouts.panel')

@section('title', 'Registrar Pago de Cuotas')

@section('encabezado')
    <x-header-with-title :titulo="\App\Http\Controllers\Panel\Finanzas\PagoCuotaController::ENCABEZADO" />
@endsection

@php
    $porcentaje = $beca ? (float) $beca->valor_beca : 0;
    $fueraDeAlcance = 'Fuera del alcance del prototipo';
    $primerMedio = array_key_first($medios);
@endphp

@section('contenido')
    <form action="{{ route('panel.pagos.store', $alumno) }}" method="POST" novalidate data-pago-cuotas data-porcentaje-beca="{{ $porcentaje }}">
        @csrf
        <div class="card las-tarjeta mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                    <h2 class="las-subtitulo las-subtitulo--tarjeta m-0">Datos del Alumno</h2>
                    @if ($beca)
                        <span class="las-pastilla">Beca Activa</span>
                    @endif
                </div>
                <div class="row">
                    <div class="col-12 col-md-3">
                        <x-form.input name="legajo" label="Legajo" :value="$alumno->nro_legajo ?? '—'" readonly tabindex="-1" />
                    </div>
                    <div class="col-12 col-md-4">
                        <x-form.input name="apellido_nombre" label="Apellido y Nombre" :value="$alumno->usuario->nombre_completo" readonly tabindex="-1" />
                    </div>
                    <div class="col-12 col-md-5">
                        <x-form.input name="carrera" label="Carrera" :value="$alumno->carrera->nombre_carrera" readonly tabindex="-1" />
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-lg-6 mb-3">
                <div class="las-marco">
                    <h3 class="las-pastilla las-marco__titulo">Cuotas Pagadas</h3>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="las-thead">
                                <tr><th scope="col">Fecha</th><th scope="col">Concepto</th><th scope="col">Importe</th><th scope="col">Factura</th><th scope="col"><span class="sr-only">Opciones</span></th></tr>
                            </thead>
                            <tbody>
                                @forelse ($pagadas as $detalle)
                                    <tr>
                                        <td>{{ $detalle->pago->fecha_pago->format('d/m/Y') }}</td>
                                        <td>{{ mb_strtoupper($detalle->cuota->mes->descripcion) }}</td>
                                        <td>{{ \App\Support\Moneda::formatear($detalle->monto_aplicado) }}</td>
                                        <td class="text-nowrap">Fac. {{ $detalle->pago->factura?->numeroFormateado() }}</td>
                                        <td><x-btn-accion icono="fas fa-ellipsis-v" texto="Más opciones" color="link" :deshabilitado="$fueraDeAlcance" /></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-muted text-center">Sin cuotas pagadas.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6 mb-3">
                <div class="las-marco">
                    <h3 class="las-pastilla las-marco__titulo">Cuotas a pagar</h3>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="las-thead">
                                <tr><th scope="col">Fecha</th><th scope="col">Concepto</th><th scope="col">Año</th><th scope="col">Importe</th><th scope="col" class="text-center">Cobrar</th><th scope="col"><span class="sr-only">Opciones</span></th></tr>
                            </thead>
                            <tbody>
                                @foreach ($pendientes as $cuota)
                                    @php $concepto = mb_strtoupper($cuota->mes->descripcion); @endphp
                                    <tr>
                                        <td>{{ $vencimientos[$cuota->id] }}</td>
                                        <td>{{ $concepto }}</td>
                                        <td>{{ $cuota->anio }}</td>
                                        <td>{{ \App\Support\Moneda::formatear($cuota->monto) }}</td>
                                        <td class="text-center">
                                            <div class="icheck-primary icheck-las d-inline-block">
                                                <input type="checkbox" name="cuotas[]" id="cuota_{{ $cuota->id }}" value="{{ $cuota->id }}" data-monto="{{ $cuota->monto }}"
                                                       @checked(in_array((string) $cuota->id, array_map('strval', (array) old('cuotas', [])), true))>
                                                <label for="cuota_{{ $cuota->id }}"><span class="sr-only">Cobrar {{ $concepto }} {{ $cuota->anio }}</span></label>
                                            </div>
                                        </td>
                                        <td><x-btn-accion icono="fas fa-ellipsis-v" texto="Más opciones" color="link" :deshabilitado="$fueraDeAlcance" /></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @error('cuotas')
            <div class="invalid-feedback d-block" role="alert">{{ $message }}</div>
        @enderror

        <p class="las-ayuda">Se seleccionan con 'Cobrar' las cuotas a incluir en el pago. Si el Alumno posee una Beca activa, se aplica la bonificacion sobre el monto original y el saldo pendiente se recalcula segun las cuotas seleccionadas.</p>

        <div class="row">
            @foreach (['original' => 'Monto Original', 'bonificacion' => 'Bonificacion (Beca)', 'total' => 'Total a Pagar', 'saldo' => 'Saldo Pendiente'] as $clave => $titulo)
                <div class="col-12 col-sm-6 col-lg-3 mb-3">
                    <label for="resumen-{{ $clave }}" class="las-etiqueta-resumen">{{ $titulo }}</label>
                    <input type="text" id="resumen-{{ $clave }}" @class(['form-control', 'las-bonificacion' => $clave === 'bonificacion', 'font-weight-bold' => $clave === 'total'])
                           value="$0" readonly tabindex="-1" data-resumen="{{ $clave }}" aria-live="polite">
                </div>
            @endforeach
        </div>

        <div class="row justify-content-end align-items-start">
            <div class="col-12 col-md-4 mb-2">
                <select id="forma-pago" class="form-control las-select-bordo" aria-label="Forma de pago" data-forma-pago>
                    @foreach ($medios as $id => $medio)
                        <option value="{{ $id }}" @selected((string) old('medio_pago_id', $primerMedio) === (string) $id)>{{ $medio }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2 mb-2">
                <input type="text" id="monto-forma-pago" class="form-control" value="0" inputmode="decimal" aria-label="Monto" data-monto-forma-pago>
            </div>
            <div class="col-6 col-md-2 mb-2">
                <button type="button" class="btn btn-las btn-block font-weight-bold" data-agregar>Agregar</button>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-sm-6 col-lg-4 mb-3">
                <label for="importe-medio" class="las-etiqueta-resumen" data-importe-label>Importe en {{ $medios[old('medio_pago_id', $primerMedio)] ?? '' }}</label>
                <input type="text" id="importe-medio" class="form-control" value="{{ old('importe', 0) }}" readonly tabindex="-1" data-importe-texto>
                @error('importe')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
                @error('medio_pago_id')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <input type="hidden" name="medio_pago_id" value="{{ old('medio_pago_id') }}" data-medio-agregado>
        <input type="hidden" name="importe" value="{{ old('importe') }}" data-importe>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-las las-btn-principal px-5" data-registrar-pago disabled>Registrar Pago</button>
        </div>
    </form>
@endsection
