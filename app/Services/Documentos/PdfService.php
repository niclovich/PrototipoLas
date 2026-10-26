<?php

namespace App\Services\Documentos;

use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DocumentoPdf;
use Illuminate\Support\Carbon;

/**
 * Base para los comprobantes en PDF (dompdf): fecha en español y QR a la
 * ruta pública de validación. Cada CU arma su vista con el layout pdf.layout.
 */
class PdfService
{
    public function __construct(private readonly QrService $qr) {}

    /**
     * @param  array<string, mixed>  $datos
     */
    public function generar(string $vista, array $datos, ?string $urlValidacion = null): DocumentoPdf
    {
        return Pdf::loadView($vista, [
            ...$datos,
            'fechaEmision' => $this->fechaEnEspanol(now()),
            'qr' => $urlValidacion ? $this->qr->dataUri($urlValidacion) : null,
            'urlValidacion' => $urlValidacion,
        ])->setPaper('a4');
    }

    public function fechaEnEspanol(Carbon $fecha): string
    {
        return $fecha->locale('es')->translatedFormat('j \d\e F \d\e Y');
    }
}
