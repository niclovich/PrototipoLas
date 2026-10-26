<?php

namespace App\Services\Documentos;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/**
 * Códigos QR con bacon/bacon-qr-code 3 (SVG, sin dependencias de imagen).
 * Se usa en los PDF que apuntan a la ruta pública de validación.
 */
class QrService
{
    public function svg(string $contenido, int $tamanio = 160): string
    {
        $writer = new Writer(new ImageRenderer(new RendererStyle($tamanio, 1), new SvgImageBackEnd));

        return $writer->writeString($contenido);
    }

    /** Data URI para <img src="..."> en vistas y en dompdf. */
    public function dataUri(string $contenido, int $tamanio = 160): string
    {
        return 'data:image/svg+xml;base64,'.base64_encode($this->svg($contenido, $tamanio));
    }
}
