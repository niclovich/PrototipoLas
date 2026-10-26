<?php

namespace Tests\Feature\Auth;

use App\Enums\RolSistema;
use App\Models\User;
use App\Notifications\DefinirContrasenia;
use App\Rules\ContraseniaSegura;
use App\Services\Usuarios\UsuarioService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

class DefinirPasswordTest extends TestCase
{
    use ConRolesYPermisos;

    private function crearConLink(): array
    {
        Notification::fake();
        $usuario = app(UsuarioService::class)->crear([
            'dni_usuario' => '30111222',
            'apellido_usuario' => 'Pérez',
            'nombre_usuario' => 'Ana',
            'email' => 'ana@las.test',
        ], RolSistema::Cajero->value, $this->superAdmin());

        $url = null;
        Notification::assertSentTo($usuario, DefinirContrasenia::class, function (DefinirContrasenia $notificacion) use (&$url) {
            $url = $notificacion->url;

            return $notificacion instanceof ShouldQueue && $notificacion->horas === 48;
        });

        return [$usuario, $url];
    }

    private function definir(string $url, string $password = 'MiClave123')
    {
        return $this->post($url, ['password' => $password, 'password_confirmation' => $password]);
    }

    public function test_el_alta_crea_usuario_sin_contrasenia_y_envia_link(): void
    {
        [$usuario, $url] = $this->crearConLink();

        $this->assertNull($usuario->fresh()->password);
        $this->assertTrue($usuario->hasRole(RolSistema::Cajero->value));
        $this->assertStringContainsString('signature=', $url);
    }

    public function test_link_valido_permite_definir_la_contrasenia(): void
    {
        [$usuario, $url] = $this->crearConLink();

        $this->get($url)->assertOk()->assertSee('Definir contraseña')->assertSee('ana@las.test');
        $this->definir($url)->assertRedirect(route('login'))->assertSessionHas('success');

        $usuario->refresh();
        $this->assertTrue(Hash::check('MiClave123', $usuario->password));
        $this->assertTrue($usuario->hasVerifiedEmail());
    }

    public function test_exige_contrasenia_segura(): void
    {
        [, $url] = $this->crearConLink();

        $this->definir($url, 'sinmayuscula1')->assertSessionHasErrors(['password' => ContraseniaSegura::MENSAJE]);
    }

    public function test_link_vencido_devuelve_403(): void
    {
        [, $url] = $this->crearConLink();

        $this->travel(49)->hours();

        $this->get($url)->assertForbidden()->assertSee('El enlace venció o no es válido');
    }

    public function test_link_reutilizado_devuelve_403(): void
    {
        [, $url] = $this->crearConLink();
        $this->definir($url);

        $this->get($url)->assertForbidden()->assertSee('Este enlace ya fue utilizado');
        $this->definir($url, 'OtraClave99')->assertForbidden();
    }

    public function test_link_alterado_devuelve_403(): void
    {
        [$usuario, $url] = $this->crearConLink();
        $otro = User::factory()->sinContrasenia()->create();

        $this->get(str_replace("/{$usuario->id}/", "/{$otro->id}/", $url))->assertForbidden();
    }
}
