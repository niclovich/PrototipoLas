<?php

namespace Tests\Feature\Alumnos;

use App\Enums\RolSistema;
use App\Http\Requests\Alumnos\EncuestaSocioeconomicaRequest;
use App\Models\EncuestaSocioeconomica;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

/** CU 9 paso 5: contrato de P3 Encuesta socioeconómica (CU1). */
class EncuestaSocioeconomicaRequestTest extends TestCase
{
    use ConRolesYPermisos, ValidaRequests;

    private User $preceptor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->preceptor = $this->crearUsuario(RolSistema::Preceptor);
    }

    public function test_acepta_encuesta_valida(): void
    {
        [$request, $errores] = $this->resolver(EncuestaSocioeconomicaRequest::class, [
            'trabaja' => '1', 'sabe_usar_pc' => '1',
            'nivel_estudios_madre' => 'Sin estudios formales', 'nivel_estudios_padre' => 'Secundario incompleto',
            'idiomas' => ['Español', 'Guaraní'],
        ]);

        $this->assertSame([], $errores);
        $datos = $request->datosEncuesta();
        $this->assertTrue($datos['trabaja']);
        $this->assertTrue($datos['sabe_usar_pc']);
        $this->assertFalse($datos['vive_solo']);
        $this->assertSame(['Español', 'Guaraní'], $datos['idiomas']);
    }

    public function test_checkbox_ausente_es_false_y_nada_es_obligatorio(): void
    {
        [$request, $errores] = $this->resolver(EncuestaSocioeconomicaRequest::class, []);

        $this->assertSame([], $errores);
        $datos = $request->datosEncuesta();
        $this->assertCount(13, EncuestaSocioeconomica::PREGUNTAS);
        foreach (EncuestaSocioeconomica::PREGUNTAS as $pregunta) {
            $this->assertFalse($datos[$pregunta], $pregunta);
        }
        $this->assertSame([], $datos['idiomas']);
    }

    public function test_rechaza_valores_no_booleanos_presentes(): void
    {
        $errores = $this->errores(EncuestaSocioeconomicaRequest::class, ['trabaja' => 'quizás', 'idiomas' => 'Español']);

        $this->assertArrayHasKey('trabaja', $errores);
        $this->assertSame([EncuestaSocioeconomicaRequest::MENSAJE_IDIOMAS], $errores['idiomas']);
    }

    public function test_rechaza_valores_fuera_de_lista(): void
    {
        $errores = $this->errores(EncuestaSocioeconomicaRequest::class, [
            'nivel_estudios_madre' => 'Doctorado', 'idiomas' => ['Español', 'Klingon'],
        ]);

        $this->assertSame([EncuestaSocioeconomicaRequest::MENSAJE_NIVEL], $errores['nivel_estudios_madre']);
        $this->assertSame([EncuestaSocioeconomicaRequest::MENSAJE_IDIOMAS], $errores['idiomas.1']);
        $this->assertSame('Seleccione un nivel de estudios de la lista.', EncuestaSocioeconomicaRequest::MENSAJE_NIVEL);
        $this->assertSame('Seleccione idiomas de la lista.', EncuestaSocioeconomicaRequest::MENSAJE_IDIOMAS);
    }

    public function test_autorizacion(): void
    {
        $this->assertSame([], $this->errores(EncuestaSocioeconomicaRequest::class, [], $this->crearUsuario(RolSistema::AuxiliarAdministrativo)));

        $this->expectException(AuthorizationException::class);
        $this->errores(EncuestaSocioeconomicaRequest::class, [], $this->crearUsuario(RolSistema::Cajero));
    }
}
