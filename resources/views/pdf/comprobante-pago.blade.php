{{--
    Comprobante de Pago de CU7 (Anexo_Comprobante_de_Pago_documento.png, Fig. 88): Original y Duplicado.
    Comprobante interno (§7-6): número 0001-XXXXXXXX y QR de validación en lugar del código de barras; sin CAE.
--}}
@php
    $c = config('las.comprobante');
    $pago = $factura->pago;
    $alumno = $pago->alumno;
    $usuario = $alumno->usuario;
    $monto = fn ($v) => '$'.number_format((float) $v, 2, ',', '.');
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Comprobante {{ $factura->numeroFormateado() }}</title>
    <style>
        @page { margin: 0.8cm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #222; }
        .hojas { width: 100%; border-collapse: collapse; }
        .hoja { width: 50%; vertical-align: top; padding: 6px 12px; }
        .hoja + .hoja { border-left: 3px solid #8B2A32; }
        h2 { text-align: center; font-size: 13px; margin: 0 0 8px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 2px 3px; vertical-align: top; }
        .logo { width: 56px; height: 56px; border-radius: 28px; background: #8B2A32; }
        .tipo { font-size: 15px; font-weight: bold; text-align: center; }
        .codigo { text-align: center; color: #666; margin-bottom: 6px; }
        .b { font-weight: bold; }
        .sep { border-top: 1px solid #ddd; margin: 6px 0; }
        .items th { text-align: left; padding: 3px; }
        .total { font-size: 13px; font-weight: bold; }
        .qr { width: 80px; height: 80px; }
    </style>
</head>
<body>
<table class="hojas">
    <tr>
        @foreach (['ORIGINAL', 'DUPLICADO'] as $copia)
            <td class="hoja">
                <h2>{{ $copia }}</h2>
                <table>
                    <tr>
                        <td style="width: 50%; border-right: 1px solid #333;">
                            <div class="logo"></div>
                            <p class="b">{{ $c['razon_social'] }}</p>
                            <p class="b">Dir.: {{ $c['direccion'] }}</p>
                            <p class="b">{{ $c['condicion_iva'] }}</p>
                            <p class="b">CUIT {{ $c['cuit'] }}</p>
                        </td>
                        <td>
                            <div class="tipo">{{ $c['tipo'] }}</div>
                            <div class="codigo">Código: {{ $c['codigo'] }}</div>
                            <table>
                                <tr><td class="b">Fact.</td><td class="b">{{ $factura->numeroFormateado() }}</td></tr>
                                <tr><td class="b">Fecha de Emision:</td><td class="b">{{ $factura->fecha_emision->format('d/m/Y') }}</td></tr>
                                <tr><td class="b">Ing. Brutos:</td><td class="b">{{ $c['ingresos_brutos'] }}</td></tr>
                                <tr><td class="b">Fecha Inicio Act.</td><td class="b">{{ $c['inicio_actividades'] }}</td></tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <table>
                    <tr><td class="b" style="width: 32%;">Legajo:</td><td>{{ $alumno->nro_legajo ?? '—' }}</td><td class="b">Con. Fiscal:</td><td>CONSUMIDOR FINAL</td></tr>
                    <tr><td class="b">Apellido N. / Razón Social:</td><td colspan="3">{{ mb_strtoupper($usuario->apellido_usuario.', '.$usuario->nombre_usuario) }}</td></tr>
                    <tr><td class="b">Domicilio:</td><td colspan="3">{{ mb_strtoupper($usuario->direccion_usuario ?? '—') }}</td></tr>
                </table>
                <div class="sep"></div>
                <table class="items">
                    <tr><th>Cod.</th><th>Descripcion</th><th>Precio</th><th>Sub Total</th></tr>
                    @foreach ($pago->detalles as $detalle)
                        <tr>
                            <td>{{ $detalle->cuota->id }}</td>
                            <td>{{ mb_strtoupper($detalle->cuota->mes->descripcion) }} {{ $detalle->cuota->anio }}</td>
                            <td>{{ $monto($detalle->cuota->monto) }}</td>
                            <td>{{ $monto($detalle->monto_aplicado) }}</td>
                        </tr>
                    @endforeach
                    @if ($pago->detalles->sum('monto_bonificado') > 0)
                        <tr><td></td><td colspan="2">Bonificación (Beca)</td><td>-{{ $monto($pago->detalles->sum('monto_bonificado')) }}</td></tr>
                    @endif
                </table>
                <table style="margin-top: 10px;">
                    <tr>
                        <td style="width: 90px;">@if ($qr)<img class="qr" src="{{ $qr }}" alt="QR de validación">@endif</td>
                        <td style="vertical-align: middle;">Total: <span class="total">{{ $monto($pago->monto_total) }}</span></td>
                    </tr>
                </table>
                <div style="font-size: 7px; color: #555;">{{ $urlValidacion }}</div>
                <table style="margin-top: 6px;">
                    <tr><td class="b">CAE N°:</td><td>—</td><td class="b">Fch. Vto:</td><td>—</td></tr>
                </table>
            </td>
        @endforeach
    </tr>
</table>
</body>
</html>
