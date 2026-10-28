<?php

namespace Tests\Feature\Alumnos;

use App\Enums\RolSistema;
use App\Http\Requests\Alumnos\DatosPersonalesAlumnoRequest;
use App\Models\Localidad;
use App\Models\Provincia;
use App\Models\User;
use App\Services\Usuarios\UsuarioService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

/** CU 9 paso 3: contrato de P2 Datos Personales (CU1). */
class DatosPersonalesAlumnoRequestTest extends TestCase
{
    use ConRolesYPermisos, ValidaRequests;

    private User $preceptor;

    private Localidad $localidad;

    protected function setUp(): void
    {
        parent::setUp();
        $this->preceptor = $this->crearUsuario(RolSistema::Preceptor);
        $this->localidad = Localidad::factory()->create();
    }

    private function minimos(array $cambios = []): array
    {
        return [
            'dni_usuario' => '40123456',
            'apellido_usuario' => 'Pérez',
            'nombre_usuario' => 'Juan',
            'email' => 'juan@correo.com',
            'provincia_id' => $this->localidad->provincia_id,
            'localidad_id' => $this->localidad->id,
            ...$cambios,
        ];
    }

    public function test_acepta_datos_minimos_y_completos(): void
    {
        $this->assertSame([], $this->errores(DatosPersonalesAlumnoRequest::class, $this->minimos()));

        [$request, $errores] = $this->resolver(DatosPersonalesAlumnoRequest::class, $this->minimos([
            'dni_usuario' => '40.123.456', 'email' => 'Juan@Correo.COM', 'fecha_nacimiento' => '13/11/1998', 'sexo' => 'Masculino',
            'estado_civil' => 'Soltero/a', 'telefono_usuario' => '387 4000000', 'celular_usuario' => '+54 387 5000000',
            'direccion_usuario' => 'Belgrano 100', 'barrio_usuario' => 'Centro',
        ]), null, ['foto' => UploadedFile::fake()->image('foto.jpg')]);

        $this->assertSame([], $errores);
        $datos = $request->datosUsuario();
        $this->assertSame('40123456', $datos['dni_usuario']);
        $this->assertSame('juan@correo.com', $datos['email']);
        $this->assertSame('1998-11-13', $datos['fecha_nacimiento']);
        $this->assertArrayNotHasKey('provincia_id', $datos);
        $this->assertArrayNotHasKey('foto', $datos);
    }

    /** @return array<string, array{array<string, mixed>, string}> */
    public static function invalidos(): array
    {
        return [
            'sin apellido' => [['apellido_usuario' => ''], 'apellido_usuario'],
            'nombre con números' => [['nombre_usuario' => 'Juan2'], 'nombre_usuario'],
            'sin DNI' => [['dni_usuario' => ''], 'dni_usuario'],
            'DNI corto' => [['dni_usuario' => '123'], 'dni_usuario'],
            'sin correo' => [['email' => ''], 'email'],
            'correo mal formado' => [['email' => 'sin-arroba'], 'email'],
            'fecha futura' => [['fecha_nacimiento' => '01/01/2999'], 'fecha_nacimiento'],
            'fecha con otro formato' => [['fecha_nacimiento' => '1998-11-13'], 'fecha_nacimiento'],
            'sexo fuera del Enum' => [['sexo' => 'Otro'], 'sexo'],
            'estado civil fuera del Enum' => [['estado_civil' => 'Soltero'], 'estado_civil'],
            'teléfono con letras' => [['telefono_usuario' => 'abc'], 'telefono_usuario'],
            'celular corto' => [['celular_usuario' => '123'], 'celular_usuario'],
            'sin provincia' => [['provincia_id' => ''], 'provincia_id'],
            'sin localidad' => [['localidad_id' => ''], 'localidad_id'],
        ];
    }

    #[DataProvider('invalidos')]
    public function test_datos_invalidos(array $cambios, string $campo): void
    {
        $this->assertArrayHasKey($campo, $this->errores(DatosPersonalesAlumnoRequest::class, $this->minimos($cambios)));
    }

    public function test_localidad_de_otra_provincia_y_foto_invalida(): void
    {
        $errores = $this->errores(DatosPersonalesAlumnoRequest::class, $this->minimos(['provincia_id' => Provincia::factory()->create()->id]),
            null, ['foto' => UploadedFile::fake()->create('cv.pdf', 10, 'application/pdf')]);

        $this->assertSame(['Seleccione una localidad de la provincia elegida.'], $errores['localidad_id']);
        $this->assertArrayHasKey('foto', $errores);

        $grande = UploadedFile::fake()->image('foto.jpg')->size(config('las.foto_max_kb') + 1);
        $this->assertArrayHasKey('foto', $this->errores(DatosPersonalesAlumnoRequest::class, $this->minimos(), null, ['foto' => $grande]));
    }

    public function test_rechaza_correo_y_dni_duplicados(): void
    {
        $this->crearUsuario(RolSistema::Docente, ['email' => 'juan@correo.com', 'dni_usuario' => '40123456']);

        $errores = $this->errores(DatosPersonalesAlumnoRequest::class, $this->minimos(['email' => 'JUAN@correo.com']));

        $this->assertSame([UsuarioService::MENSAJE_CORREO_REGISTRADO], $errores['email']);
        $this->assertSame([UsuarioService::MENSAJE_DNI_REGISTRADO], $errores['dni_usuario']);
    }

    public function test_solo_autoriza_a_quien_tiene_crear_usuario_alumno(): void
    {
        $this->assertSame([], $this->errores(DatosPersonalesAlumnoRequest::class, $this->minimos(), $this->crearUsuario(RolSistema::AuxiliarAdministrativo)));

        foreach ([RolSistema::Cajero, RolSistema::Alumno, RolSistema::Coordinador] as $rol) {
            try {
                $this->errores(DatosPersonalesAlumnoRequest::class, $this->minimos(), $this->crearUsuario($rol));
                $this->fail("{$rol->value} no debía estar autorizado.");
            } catch (AuthorizationException) {
                $this->addToAssertionCount(1);
            }
        }
    }
}
