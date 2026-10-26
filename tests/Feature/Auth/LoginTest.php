<?php

namespace Tests\Feature\Auth;

use App\Enums\EstadoCuenta;
use App\Enums\EstadoLegajo;
use App\Enums\RolSistema;
use App\Models\Alumno;
use App\Models\User;
use App\Services\Auth\AutenticacionService;
use App\Services\Usuarios\UsuarioService;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use ConRolesYPermisos;

    private function intentar(string $email, string $password = 'Clave1234')
    {
        return $this->from(route('login'))->post(route('login'), ['email' => $email, 'password' => $password]);
    }

    public function test_muestra_la_pantalla_de_login_en_espanol(): void
    {
        $this->get('/')->assertRedirect('/login');

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Correo electrónico')
            ->assertSee('Ingresar')
            ->assertSee('¿Olvidó su contraseña?');
    }

    public function test_ingresa_con_correo_y_contrasenia(): void
    {
        $usuario = $this->crearUsuario(RolSistema::Preceptor, ['email' => 'preceptor@las.test']);

        $this->intentar('PRECEPTOR@las.test')->assertRedirect('/panel');

        $this->assertAuthenticatedAs($usuario);
    }

    public function test_rechaza_contrasenia_incorrecta(): void
    {
        $this->crearUsuario(RolSistema::Preceptor, ['email' => 'preceptor@las.test']);

        $this->intentar('preceptor@las.test', 'Otra1234')
            ->assertSessionHasErrors(['email' => 'Estas credenciales no coinciden con nuestros registros.']);

        $this->assertGuest();
    }

    public function test_rechaza_cuenta_inactiva(): void
    {
        User::factory()->inactivo()->create(['email' => 'baja@las.test']);

        $this->intentar('baja@las.test')->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_el_dni_no_sirve_para_ingresar(): void
    {
        $this->crearUsuario(RolSistema::Preceptor, ['dni_usuario' => '30111222']);

        $this->intentar('30111222')->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_usuario_sin_contrasenia_definida_no_ingresa(): void
    {
        User::factory()->sinContrasenia()->create(['email' => 'nuevo@las.test']);

        $this->intentar('nuevo@las.test')->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_alumno_sin_legajo_activo_no_ingresa(): void
    {
        $this->crearUsuario(RolSistema::Alumno, ['email' => 'alumno@las.test']);

        $this->intentar('alumno@las.test')
            ->assertSessionHasErrors(['email' => AutenticacionService::MENSAJE_NO_ACTIVO]);

        $this->assertGuest();
    }

    public function test_alumno_con_legajo_cerrado_no_ingresa(): void
    {
        $usuario = $this->crearUsuario(RolSistema::Alumno, ['email' => 'alumno@las.test']);
        Alumno::factory()->for($usuario, 'usuario')->create(['estado_legajo' => EstadoLegajo::Cerrado]);

        $this->intentar('alumno@las.test')
            ->assertSessionHasErrors(['email' => AutenticacionService::MENSAJE_NO_ACTIVO]);

        $this->assertGuest();
    }

    public function test_alumno_con_legajo_activo_ingresa(): void
    {
        $usuario = $this->crearUsuario(RolSistema::Alumno, ['email' => 'alumno@las.test']);
        Alumno::factory()->for($usuario, 'usuario')->create(['estado_legajo' => EstadoLegajo::Cerrado]);
        Alumno::factory()->for($usuario, 'usuario')->create(['estado_legajo' => EstadoLegajo::Activo, 'nro_legajo' => 10001]);

        $this->intentar('alumno@las.test')->assertRedirect('/panel');

        $this->assertAuthenticatedAs($usuario);
    }

    public function test_bloquea_al_sexto_intento_en_un_minuto(): void
    {
        $this->crearUsuario(RolSistema::Preceptor, ['email' => 'preceptor@las.test']);

        foreach (range(1, 5) as $intento) {
            $this->intentar('preceptor@las.test', 'Mala1234');
        }

        $respuesta = $this->intentar('preceptor@las.test');

        $respuesta->assertSessionHasErrors('email');
        $this->assertStringContainsString('Demasiados intentos', session('errors')->first('email'));
        $this->assertGuest();
    }

    public function test_el_limite_es_por_correo_e_ip(): void
    {
        $this->crearUsuario(RolSistema::Preceptor, ['email' => 'a@las.test']);
        $otro = $this->crearUsuario(RolSistema::Cajero, ['email' => 'b@las.test']);

        foreach (range(1, 6) as $intento) {
            $this->intentar('a@las.test', 'Mala1234');
        }

        // Mismo IP, otro correo: no queda bloqueado.
        $this->intentar('b@las.test')->assertRedirect('/panel');
        $this->assertAuthenticatedAs($otro);
        $this->post(route('logout'));

        // Mismo correo desde otra IP: tampoco.
        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.99'])
            ->post(route('login'), ['email' => 'a@las.test', 'password' => 'Clave1234'])
            ->assertRedirect('/panel');
    }

    public function test_cuenta_dada_de_baja_con_sesion_abierta_es_expulsada(): void
    {
        $cajero = $this->crearUsuario(RolSistema::Cajero);
        $this->actingAs($cajero)->get(route('panel.cajas.index'))->assertOk();

        $cajero->forceFill(['estado_cuenta' => EstadoCuenta::Inactivo])->save();

        $this->get(route('panel.cajas.index'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => AutenticacionService::MENSAJE_NO_ACTIVO]);
        $this->assertGuest();
    }

    public function test_recordarme_no_reautentica_una_cuenta_dada_de_baja(): void
    {
        $cajero = $this->crearUsuario(RolSistema::Cajero, ['email' => 'cajero@las.test']);
        $this->post(route('login'), ['email' => 'cajero@las.test', 'password' => 'Clave1234', 'remember' => 'on']);
        $tokenAnterior = $cajero->fresh()->remember_token;
        $this->post(route('logout'));
        DB::table('sessions')->insert([
            'id' => 'sesion-abierta', 'user_id' => $cajero->id, 'payload' => '', 'last_activity' => time(),
        ]);

        app(UsuarioService::class)->darDeBaja($cajero->fresh(), $this->superAdmin());

        // La cookie "Recordarme" guardaba el token anterior: ya no coincide. Las sesiones abiertas se borran.
        $this->assertNotSame($tokenAnterior, $cajero->fresh()->remember_token);
        $this->assertDatabaseMissing('sessions', ['user_id' => $cajero->id]);
    }

    public function test_logout_solo_por_post(): void
    {
        $usuario = $this->crearUsuario(RolSistema::Preceptor);

        $this->actingAs($usuario)->get('/logout')->assertStatus(405);

        $this->actingAs($usuario)->post(route('logout'))->assertRedirect('/');
        $this->assertGuest();
    }
}
