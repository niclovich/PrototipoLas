<?php

namespace Tests\Unit;

use App\Services\Documentos\QrService;
use PHPUnit\Framework\TestCase;

class QrServiceTest extends TestCase
{
    public function test_genera_svg_en_data_uri(): void
    {
        $uri = (new QrService)->dataUri('https://las.test/validar/comprobante/123');

        $this->assertStringStartsWith('data:image/svg+xml;base64,', $uri);
        $this->assertStringContainsString('<svg', base64_decode(substr($uri, strlen('data:image/svg+xml;base64,'))));
    }
}
