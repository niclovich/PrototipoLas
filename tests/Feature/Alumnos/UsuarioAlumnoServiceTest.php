<?php

namespace Tests\Feature\Alumnos;

use App\Enums\EstadoCivil;
use App\Enums\EstadoCuenta;
use App\Enums\Idioma;
use App\Enums\NivelEstudios;
use App\Enums\RolSistema;
use App\Enums\Sexo;
use App\Http\Requests\Alumnos\DatosPersonalesAlumnoRequest;
use App\Http\Requests\Alumnos\EncuestaSocioeconomicaRequest;
use App\Models\EncuestaSocioeconomica;
use App\Models\Localidad;
use App\Models\User;
use App\Services\Alumnos\UsuarioAlumnoService;
use App\Services\Auth\AutenticacionService;
use App\Services\Usuarios\UsuarioService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

/** CU 9 Registrar Datos de Usuarios Alumno (servicio que usa CU1). */
class UsuarioAlumnoServiceTest extends TestCase
{
    use ConRolesYPermisos;

    private UsuarioAlumnoService $servicio;

    private Localidad $localidad;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Notification::fake();
        $this->servicio = app(UsuarioAlumnoService::class);
        $this->localidad = Localidad::factory()->create();
        $this->actingAs($this->crearUsuario(RolSistema::Preceptor));
    }

    private function datos(array $cambios = []): array
    {
        return [
            'dni_usuario' => '40.123.456',
            'apellido_usuario' => 'Pérez',
            'nombre_usuario' => 'Juan',
            'fecha_nacimiento' => '1998-11-13',
            'sexo' => Sexo::Masculino->value,
            'estado_civil' => EstadoCivil::Soltero->value,
            'email' => 'JPerez@Correo.com',
            'telefono_usuario' => '3874000000',
            'celular_usuario' => '3875000000',
            'direccion_usuario' => 'Belgrano 100',
            'barrio_usuario' => 'Centro',
            'localidad_id' => $this->localidad->id,
            ...$cambios,
        ];
    }

    private function encuesta(array $cambios = []): array
    {
        return [
            ...array_fill_keys(EncuestaSocioeconomica::PREGUNTAS, false),
            'trabaja' => true,
            'sabe_usar_pc' => true,
            'nivel_estudios_madre' => NivelEstudios::SinEstudiosFormales->value,
            'nivel_estudios_padre' => NivelEstudios::SecundarioIncompleto->value,
            'idiomas' => [Idioma::Espanol->value, Idioma::Guarani->value],
            ...$cambios,
        ];
    }

    public function test_registra_usuario_alumno(): void
    {
        $usuario = $this->servicio->registrar($this->datos(), $this->encuesta());

        $usuario->refresh();
        $this->assertSame('40123456', $usuario->dni_usuario);
        $this->assertSame('jperez@correo.com', $usuario->email);
        $this->assertSame('1998-11-13', $usuario->fecha_nacimiento->toDateString());
        $this->assertSame(Sexo::Masculino, $usuario->sexo);
        $this->assertSame(EstadoCivil::Soltero, $usuario->estado_civil);
        $this->assertSame(EstadoCuenta::Activo, $usuario->estado_cuenta);
        $this->assertTrue($usuario->fecha_alta->isToday());
        $this->assertNull($usuario->password);
        $this->assertNull($usuario->email_verified_at);
        $this->assertSame([RolSistema::Alumno->value], $usuario->getRoleNames()->all());
        $this->assertSame($this->localidad->id, $usuario->localidad_id);
        $this->assertSame('Argentina', $usuario->pais_usuario);
        $this->assertSame('Centro', $usuario->barrio_usuario);
        $this->assertNull($usuario->foto_usuario);
    }

    public function test_registra_encuesta_vinculada(): void
    {
        $usuario = $this->servicio->registrar($this->datos(), $this->encuesta());

        $encuesta = EncuestaSocioeconomica::where('user_id', $usuario->id)->sole();
        $this->assertTrue($encuesta->trabaja);
        $this->assertFalse($encuesta->vive_solo);
        $this->assertSame(NivelEstudios::SinEstudiosFormales, $encuesta->nivel_estudios_madre);
        $this->assertSame(['Español', 'Guaraní'], $encuesta->idiomas);
        $this->assertDatabaseHas('encuestas_socioeconomicas', ['user_id' => $usuario->id, 'idiomas' => 'Español, Guaraní']);
    }

    public function test_guarda_foto_en_disco_public(): void
    {
        $usuario = $this->servicio->registrar($this->datos(), $this->encuesta(), $this->fotoTemporal());

        $this->assertStringStartsWith(UsuarioAlumnoService::DIRECTORIO_FOTOS.'/', $usuario->foto_usuario);
        Storage::disk('public')->assertExists($usuario->foto_usuario);
    }

    public function test_rechaza_dni_registrado(): void
    {
        $this->crearUsuario(RolSistema::Docente, ['dni_usuario' => '40123456']);

        try {
            $this->servicio->registrar($this->datos(), $this->encuesta(), $this->fotoTemporal());
            $this->fail('Debía rechazar el DNI.');
        } catch (ValidationException $e) {
            $this->assertSame(UsuarioService::MENSAJE_DNI_REGISTRADO, $e->errors()['dni_usuario'][0]);
        }

        $this->assertSame([], Storage::disk('public')->allFiles(), 'No queda el archivo de la foto.');
    }

    /** @return array<string, array{string, string, string}> índice violado => campo y mensaje esperados */
    public static function carreras(): array
    {
        return [
            'DNI' => ['users_dni_usuario_unique', 'dni_usuario', UsuarioService::MENSAJE_DNI_REGISTRADO],
            'correo' => ['users_email_unique', 'email', UsuarioService::MENSAJE_CORREO_REGISTRADO],
        ];
    }

    #[DataProvider('carreras')]
    public function test_rechaza_duplicado_en_carrera(string $indice, string $campo, string $mensaje): void
    {
        // Simula que otro registro tomó el DNI o el correo entre la verificación y el alta (el SQL nombra a "email" igual).
        User::creating(function () use ($indice) {
            throw new UniqueConstraintViolationException('mysql', 'insert into `users` (`dni_usuario`, `email`) values (?, ?)', [],
                new \Exception("SQLSTATE[23000]: Duplicate entry 'x' for key '{$indice}'"));
        });

        try {
            $this->servicio->registrar($this->datos(), $this->encuesta(), $this->fotoTemporal());
            $this->fail('Debía rechazar el duplicado.');
        } catch (ValidationException $e) {
            $this->assertSame([$campo => [$mensaje]], $e->errors());
        }

        $this->assertSame([], Storage::disk('public')->allFiles(), 'No queda el archivo de la foto.');
        $this->assertDatabaseMissing('users', ['dni_usuario' => '40123456']);
        $this->assertSame(0, EncuestaSocioeconomica::count());
    }

    public function test_foto_temporal_se_mueve_al_confirmar_y_se_puede_descartar(): void
    {
        $temporal = $this->fotoTemporal();
        $this->assertStringStartsWith(UsuarioAlumnoService::DIRECTORIO_TEMPORAL.'/', $temporal);

        $usuario = $this->servicio->registrar($this->datos(), $this->encuesta(), $temporal);

        Storage::disk('public')->assertMissing($temporal);
        Storage::disk('public')->assertExists($usuario->foto_usuario);

        $otra = $this->fotoTemporal();
        $this->servicio->descartarFotoTemporal($otra);
        Storage::disk('public')->assertMissing($otra);
    }

    /**
     * Contrato del wizard (opción B de D7): la sesión guarda los datos validados tal como llegaron
     * (datosSesion()), en P5 se re-validan con las mismas reglas y recién después se registran.
     */
    public function test_ida_y_vuelta_por_la_sesion_del_wizard(): void
    {
        $crudos = [
            'dni_usuario' => '40.123.456', 'apellido_usuario' => 'Pérez', 'nombre_usuario' => 'Juan', 'email' => 'JPerez@Correo.com',
            'fecha_nacimiento' => '13/11/1998', 'provincia_id' => $this->localidad->provincia_id, 'localidad_id' => $this->localidad->id,
        ];
        $p2 = DatosPersonalesAlumnoRequest::create('/', 'POST', $crudos);
        $p2->setContainer(app())->setRedirector(app('redirect'))->setUserResolver(fn () => auth()->user());
        $p2->validateResolved();
        $sesion = $p2->datosSesion();

        $p3 = EncuestaSocioeconomicaRequest::create('/', 'POST', ['trabaja' => '1', 'idiomas' => ['Español']]);
        $p3->setContainer(app())->setRedirector(app('redirect'))->setUserResolver(fn () => auth()->user());
        $p3->validateResolved();
        $sesionEncuesta = $p3->validated();

        // P5: re-validación con las mismas reglas.
        $datos = Validator::make($sesion, DatosPersonalesAlumnoRequest::reglas($sesion), [], DatosPersonalesAlumnoRequest::atributosDatosUsuario())->validate();
        $encuesta = Validator::make($sesionEncuesta, EncuestaSocioeconomicaRequest::reglas())->validate();

        $usuario = $this->servicio->registrar(DatosPersonalesAlumnoRequest::paraUsuario($datos), EncuestaSocioeconomicaRequest::paraEncuesta($encuesta));

        $this->assertSame('1998-11-13', $usuario->fresh()->fecha_nacimiento->toDateString());
        $this->assertTrue($usuario->encuesta->trabaja);
        $this->assertFalse($usuario->encuesta->vive_solo);
    }

    public function test_rechaza_correo_registrado(): void
    {
        $this->crearUsuario(RolSistema::Docente, ['email' => 'jperez@correo.com']);

        try {
            $this->servicio->registrar($this->datos(), $this->encuesta());
            $this->fail('Debía rechazar el correo.');
        } catch (ValidationException $e) {
            $this->assertSame(UsuarioService::MENSAJE_CORREO_REGISTRADO, $e->errors()['email'][0]);
        }
    }

    public function test_rollback_si_falla_la_encuesta(): void
    {
        try {
            $this->servicio->registrar($this->datos(), $this->encuesta(['nivel_estudios_madre' => str_repeat('x', 300)]), $this->fotoTemporal());
            $this->fail('Debía fallar la Encuesta.');
        } catch (\Throwable) {
            // esperado
        }

        $this->assertDatabaseMissing('users', ['dni_usuario' => '40123456']);
        $this->assertSame(0, EncuestaSocioeconomica::count());
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_no_envia_notificaciones_y_queda_listo_para_el_link_de_cu2(): void
    {
        $usuario = $this->servicio->registrar($this->datos(), $this->encuesta());

        Notification::assertNothingSent();
        $this->assertTrue(app(UsuarioService::class)->linkVigente($usuario->fresh(), sha1($usuario->email)));
    }

    public function test_no_puede_ingresar_sin_legajo(): void
    {
        $usuario = $this->servicio->registrar($this->datos(), $this->encuesta());
        auth()->logout();

        // Sin contraseña no puede entrar.
        $this->post(route('login'), ['email' => 'jperez@correo.com', 'password' => 'cualquiera'])->assertSessionHasErrors('email');

        // Aun con la contraseña definida (link de CU2), sin legajo Activo tampoco.
        app(UsuarioService::class)->definirContrasenia($usuario, 'Clave2026');
        $this->post(route('login'), ['email' => 'jperez@correo.com', 'password' => 'Clave2026'])
            ->assertSessionHasErrors(['email' => AutenticacionService::MENSAJE_NO_ACTIVO]);
        $this->assertSame('Este usuario no está activo', AutenticacionService::MENSAJE_NO_ACTIVO);
        $this->assertGuest();
    }

    private function fotoTemporal(): string
    {
        return $this->servicio->guardarFotoTemporal(UploadedFile::fake()->image('foto.jpg'));
    }

    public function test_buscar_por_dni_normaliza(): void
    {
        $usuario = $this->crearUsuario(RolSistema::Docente, ['dni_usuario' => '30125478']);
        $usuarios = app(UsuarioService::class);

        $this->assertTrue($usuarios->buscarPorDni('30.125.478')->is($usuario));
        $this->assertNull($usuarios->buscarPorDni('41000000'));
        $this->assertNull($usuarios->buscarPorDni('...'));
    }

    public function test_literales_de_mensajes(): void
    {
        $this->assertSame('El Usuario Alumno se registró correctamente.', UsuarioAlumnoService::MENSAJE_REGISTRO_EXITOSO);
        $this->assertSame('Ya existe un usuario registrado con el DNI ingresado', UsuarioService::MENSAJE_DNI_REGISTRADO);
        $this->assertSame('Ya existe un usuario registrado con el correo ingresado.', UsuarioService::MENSAJE_CORREO_REGISTRADO);
    }

    public function test_auditoria_registra_causer(): void
    {
        $actor = auth()->user();
        $usuario = $this->servicio->registrar($this->datos(), $this->encuesta());

        $this->assertDatabaseHas('activity_log', ['subject_type' => User::class, 'subject_id' => $usuario->id, 'causer_id' => $actor->id, 'event' => 'created']);
        $this->assertDatabaseHas('activity_log', ['subject_type' => EncuestaSocioeconomica::class, 'causer_id' => $actor->id, 'event' => 'created']);
    }
}
