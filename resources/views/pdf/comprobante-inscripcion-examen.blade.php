{{--
    Comprobante de Inscripción a Examen de CU5 (Comprobante_de_Inscripcion.png, Fig. 82).
    Se agrega el QR de validación al pie (arquitectura §1; E17-D3).
--}}
@php
    $alumno = $detalle->alumno;
    $acta = $detalle->acta;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Comprobante de Inscripción a Examen</title>
    <style>
        @page { margin: 1.6cm 2cm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        .instituto { text-align: center; color: #8B2A32; font-weight: bold; font-size: 12px; text-transform: uppercase;
            border-bottom: 2px solid #8B2A32; padding-bottom: 8px; }
        h1 { text-align: center; font-size: 16px; margin: 18px 0 4px; }
        .emision { text-align: center; color: #888; font-style: italic; font-size: 9px; margin-bottom: 16px; }
        .tarjeta { border: 1px solid #e3d5d7; border-radius: 8px; padding: 10px 16px; margin: 0 30px 14px; }
        .tarjeta h2 { color: #8B2A32; font-size: 12px; margin: 0 0 8px; }
        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; padding: 3px 0 6px; width: 50%; }
        .etiqueta { color: #555; font-size: 9px; }
        .valor { font-weight: bold; }
        .certifica { color: #888; font-style: italic; font-size: 9px; margin: 6px 30px 10px; }
        .linea { border-top: 1px solid #e3d5d7; margin: 0 40px; }
        .qr { margin: 12px 30px 0; font-size: 8px; color: #666; }
        .qr img { width: 80px; height: 80px; }
    </style>
</head>
<body>
    <div class="instituto">{{ config('las.instituto_nombre') }}</div>
    <h1>COMPROBANTE DE INSCRIPCION A EXAMEN</h1>
    <div class="emision">Fecha de emision: {{ now()->format('d/m/Y') }} · {{ now()->format('H:i') }} hs</div>

    <div class="tarjeta">
        <h2>Datos del Alumno</h2>
        <table>
            <tr>
                <td><div class="etiqueta">Legajo</div><div class="valor">{{ $alumno->nro_legajo }}</div></td>
                <td><div class="etiqueta">DNI</div><div class="valor">{{ $alumno->usuario->dni_usuario }}</div></td>
            </tr>
            <tr>
                <td colspan="2"><div class="etiqueta">Apellido y Nombre</div><div class="valor">{{ $alumno->usuario->nombre_completo }}</div></td>
            </tr>
        </table>
    </div>

    <div class="tarjeta">
        <h2>Datos del Acta de Examen</h2>
        <table>
            <tr>
                <td><div class="etiqueta">Nro Acta</div><div class="valor">{{ $acta->nro_acta_texto }}</div></td>
                <td><div class="etiqueta">Fecha de Examen</div><div class="valor">{{ $acta->fecha_acta->format('d/m/Y') }}</div></td>
            </tr>
            <tr>
                <td colspan="2"><div class="etiqueta">Materia</div><div class="valor">{{ $acta->detallePlan->materia->nombre_materia }}</div></td>
            </tr>
            <tr>
                <td><div class="etiqueta">Modalidad</div><div class="valor">{{ $acta->modalidad->modalidad }}</div></td>
                <td><div class="etiqueta">Llamado</div><div class="valor">{{ $acta->llamado->llamado }}</div></td>
            </tr>
        </table>
    </div>

    <p class="certifica">Este comprobante certifica la inscripcion del alumno en el acta de examen indicada.</p>
    <div class="linea"></div>

    @if ($qr)
        <table class="qr">
            <tr>
                <td style="width: 90px;"><img src="{{ $qr }}" alt="Código QR de validación"></td>
                <td style="vertical-align: middle;">Valide este comprobante escaneando el código QR o ingresando a:<br>{{ $urlValidacion }}</td>
            </tr>
        </table>
    @endif
</body>
</html>
