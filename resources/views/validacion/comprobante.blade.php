{{-- Validación pública del Comprobante de Pago (QR). Solo datos mínimos: número, fecha, alumno, carrera, total y estado. --}}
@extends('validacion.layout', ['tituloPagina' => 'Validación de comprobante', 'tituloTarjeta' => 'Comprobante válido'])

@section('datos')
    <dt class="col-sm-5">Comprobante</dt><dd class="col-sm-7">{{ config('las.comprobante.tipo') }} {{ $factura->numeroFormateado() }}</dd>
    <dt class="col-sm-5">Fecha de emisión</dt><dd class="col-sm-7">{{ $factura->fecha_emision->format('d/m/Y') }}</dd>
    <dt class="col-sm-5">Alumno</dt><dd class="col-sm-7">{{ $factura->pago->alumno->usuario->nombre_completo }}</dd>
    <dt class="col-sm-5">Carrera</dt><dd class="col-sm-7">{{ $factura->pago->alumno->carrera->nombre_carrera }}</dd>
    <dt class="col-sm-5">Total</dt><dd class="col-sm-7">{{ \App\Support\Moneda::formatear($factura->pago->monto_total) }}</dd>
    <dt class="col-sm-5">Estado</dt><dd class="col-sm-7"><x-badge-estado :estado="$factura->estado_factura" /></dd>
@endsection
