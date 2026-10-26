{{-- Layout común de los comprobantes PDF (PdfService): encabezado del instituto, fecha en español y QR de validación. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>@yield('titulo')</title>
    <style>
        @page { margin: 2cm 1.6cm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        .encabezado { border-bottom: 3px solid #8B2A32; padding-bottom: 8px; margin-bottom: 16px; }
        .encabezado h1 { color: #8B2A32; font-size: 16px; margin: 0; }
        .encabezado p { margin: 2px 0 0; color: #555; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #8B2A32; color: #fff; text-align: left; padding: 5px; }
        td { border-bottom: 1px solid #ddd; padding: 5px; }
        .pie { position: fixed; bottom: 0; left: 0; right: 0; font-size: 9px; color: #666; }
        .pie img { width: 90px; height: 90px; }
    </style>
</head>
<body>
    <div class="encabezado">
        <h1>{{ config('las.instituto_nombre') }}</h1>
        <p>@yield('titulo') &middot; {{ $fechaEmision }}</p>
    </div>

    @yield('contenido')

    @if ($qr)
        <table class="pie">
            <tr>
                <td style="width: 100px; border: 0;"><img src="{{ $qr }}" alt="Código QR de validación"></td>
                <td style="border: 0;">Valide este documento escaneando el código QR o ingresando a:<br>{{ $urlValidacion }}</td>
            </tr>
        </table>
    @endif
</body>
</html>
