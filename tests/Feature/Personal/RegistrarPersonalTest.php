<?php

namespace Tests\Feature\Personal;

use App\Enums\EstadoCuenta;
use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Models\Localidad;
use App\Models\Provincia;
use App\Models\Rol;
use App\Models\User;
use App\Notifications\VerificarCorreo;
use App\Rules\ContraseniaSegura;
use App\Services\Personal\PersonalService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

/** CU 25 Registrar datos del Personal. */
class RegistrarPersonalTest extends TestCase
{
    use ConRolesYPermisos;

    private User $director;

    private Localidad $localidad;

    protected function setUp(): void
    {
        parent::setUp();
        $this->director = User::factory()->personal(RolSistema::DirectorGeneral)->create();
        $this->localidad = Localidad::factory()->create(['localidad' => 'Cafayate']);
    }

    private function datos(array $cambios = []): array
    {
        return [
            'dni_usuario' => '30.125.478',
            'apellido_usuario' => 'González',
            'nombre_usuario' => 'María',
            'telefono_usuario' => '3814567890',
            'email' => 'MGonzalez@Instituto.edu',
            'direccion_usuario' => 'San Martín 123',
            'provincia_id' => $this->localidad->provincia_id,
            'localidad_id' => $this->localidad->id,
            'rol_id' => Rol::findByName(RolSistema::Docente->value)->id,
            'password' => 'Clave2026',
            ...$cambios,
        ];
    }

    private function registrar(array $cambios = [])
    {
        return $this->actingAs($this->director)->from(route('panel.personal.create'))->post(route('panel.personal.store'), $this->datos($cambios));
    }

    public function test_dg_ve_el_formulario(): void
    {
        $this->get(route('panel.personal.create'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->personal(RolSistema::Cajero)->create())->get(route('panel.personal.create'))->assertForbidden();

        $this->actingAs($this->director)->get(route('panel.personal.create'))->assertOk()
            ->assertSeeInOrder(['GESTIÓN DE PERSONAL', 'Registrar Personal', '1 · DATOS PERSONALES', 'DNI', 'Ej: 30125478', 'Apellido', 'Nombre',
                'Teléfono', 'Correo', 'Dirección', 'Provincia', 'Seleccione una provincia', 'Localidad', 'Seleccione una localidad',
                '2 · ROL Y CUENTA DE ACCESO', 'Rol', 'Seleccione un rol', 'Descripción del Rol', 'Contraseña', ContraseniaSegura::AYUDA,
                'Cancelar', 'Registrar Personal']);
    }

    public function test_solo_ofrece_roles_del_personal(): void
    {
        $html = $this->actingAs($this->director)->get(route('panel.personal.create'))->getContent();

        foreach (RolSistema::valoresDelPersonal() as $rol) {
            $this->assertStringContainsString('>'.$rol.'</option>', $html);
        }
        $this->assertStringNotContainsString('>Alumno</option>', $html);
        $this->assertStringNotContainsString('>SUPERADMIN</option>', $html);
    }

    public function test_registra_personal(): void
    {
        Notification::fake();

        $this->registrar()->assertRedirect(route('panel.personal.confirmacion'));
        $this->followRedirects($this->registrar(['dni_usuario' => '30999888', 'email' => 'otra@instituto.edu']))
            ->assertSee(PersonalService::CONFIRMACION_REGISTRO)->assertSee('Aceptar');

        $usuario = User::where('dni_usuario', '30125478')->sole();
        $this->assertSame('mgonzalez@instituto.edu', $usuario->email);
        $this->assertTrue(Hash::check('Clave2026', $usuario->password));
        $this->assertTrue($usuario->fecha_alta->isToday());
        $this->assertSame(EstadoCuenta::Activo, $usuario->estado_cuenta);
        $this->assertSame([RolSistema::Docente->value], $usuario->getRoleNames()->all());
        $this->assertSame($this->localidad->id, $usuario->localidad_id);
        $this->assertSame('3814567890', $usuario->telefono_usuario);
        $this->assertSame('San Martín 123', $usuario->direccion_usuario);
        Notification::assertSentTo($usuario, VerificarCorreo::class);
    }

    public function test_no_registra_con_dni_existente(): void
    {
        $this->crearUsuario(RolSistema::Alumno, ['dni_usuario' => '30125478']);

        $this->registrar()->assertSessionHasErrors(['dni_usuario' => PersonalService::MENSAJE_DNI_REGISTRADO]);
        $this->assertSame('Ya existe un usuario registrado con el DNI ingresado', PersonalService::MENSAJE_DNI_REGISTRADO);

        $this->followRedirects($this->registrar())->assertSee('DNI ya registrado')->assertSee('data-abrir', false);
        $this->assertSame(1, User::where('dni_usuario', '30125478')->count());
    }

    public function test_verificar_dni(): void
    {
        $this->crearUsuario(RolSistema::Docente, ['dni_usuario' => '30125478']);

        $this->actingAs($this->director)->postJson(route('panel.personal.verificar-dni'), ['dni_usuario' => '30.125.478'])->assertExactJson(['existe' => true]);
        $this->actingAs($this->director)->postJson(route('panel.personal.verificar-dni'), ['dni_usuario' => '41000000'])->assertExactJson(['existe' => false]);
    }

    /** @return array<string, array{string}> */
    public static function contraseniasInvalidas(): array
    {
        return ['corta' => ['Abc123'], 'sin mayúscula' => ['abcdefg1'], 'sin número' => ['Abcdefgh'], 'vacía' => ['']];
    }

    #[DataProvider('contraseniasInvalidas')]
    public function test_contrasenia_invalida(string $password): void
    {
        $this->registrar(['password' => $password])->assertSessionHasErrors(['password' => ContraseniaSegura::MENSAJE]);
        $this->assertSame('La contraseña debe tener al menos 8 caracteres, una letra mayúscula y un número', ContraseniaSegura::MENSAJE);
        $this->followRedirects($this->registrar(['password' => $password]))->assertSee('Contraseña inválida');
        $this->assertDatabaseMissing('users', ['dni_usuario' => '30125478']);
    }

    public function test_rechaza_localidad_de_otra_provincia(): void
    {
        $otra = Provincia::factory()->create();

        $this->registrar(['provincia_id' => $otra->id])->assertSessionHasErrors(['localidad_id' => 'Seleccione una localidad de la provincia elegida.']);
    }

    public function test_rechaza_roles_que_no_son_del_personal(): void
    {
        $inactivo = Rol::create(['name' => 'Rol viejo', 'guard_name' => 'web', 'estado' => EstadoGeneral::Inactivo]);

        foreach ([Rol::findByName('Alumno')->id, Rol::findByName('SUPERADMIN')->id, 99999, $inactivo->id] as $rolId) {
            $this->registrar(['rol_id' => $rolId])->assertSessionHasErrors(['rol_id' => 'Seleccione un rol del personal.']);
        }
    }

    public function test_rechaza_correo_duplicado_y_datos_con_formato_invalido(): void
    {
        $this->crearUsuario(RolSistema::Docente, ['email' => 'mgonzalez@instituto.edu']);

        $this->registrar(['telefono_usuario' => 'abc', 'apellido_usuario' => 'G0nz4lez'])->assertSessionHasErrors([
            'email' => PersonalService::MENSAJE_CORREO_REGISTRADO,
            'telefono_usuario',
            'apellido_usuario',
        ]);
    }

    public function test_confirmacion_sin_mensaje_redirige_a_p1(): void
    {
        $this->actingAs($this->director)->get(route('panel.personal.confirmacion'))->assertRedirect(route('panel.personal.index'));
    }
}
