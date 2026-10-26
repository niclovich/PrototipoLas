<?php

namespace Tests\Feature\Auth;

use App\Enums\RolSistema;
use App\Notifications\RestablecerContrasenia;
use App\Rules\ContraseniaSegura;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use ConRolesYPermisos;

    public function test_muestra_la_pantalla_de_recuperacion(): void
    {
        $this->get(route('password.request'))->assertOk()->assertSee('Recuperar contraseña');
    }

    public function test_envia_notificacion_propia_encolada_y_en_espanol(): void
    {
        Notification::fake();
        $usuario = $this->crearUsuario(RolSistema::Preceptor);

        $this->post(route('password.email'), ['email' => $usuario->email])
            ->assertSessionHas('status', 'Le enviamos por correo el enlace para restablecer la contraseña.');

        Notification::assertSentTo($usuario, RestablecerContrasenia::class, function (RestablecerContrasenia $notificacion) use ($usuario) {
            $correo = $notificacion->toMail($usuario);

            return $notificacion instanceof ShouldQueue
                && str_starts_with($correo->subject, 'Restablecer contraseña')
                && $correo->actionText === 'Restablecer contraseña';
        });
    }

    public function test_restablece_la_contrasenia_con_token_valido(): void
    {
        Notification::fake();
        $usuario = $this->crearUsuario(RolSistema::Preceptor);
        $this->post(route('password.email'), ['email' => $usuario->email]);

        Notification::assertSentTo($usuario, RestablecerContrasenia::class, function (RestablecerContrasenia $notificacion) use ($usuario) {
            $this->get(route('password.reset', ['token' => $notificacion->token, 'email' => $usuario->email]))
                ->assertOk()->assertSee('Restablecer contraseña');

            $this->post(route('password.update'), [
                'token' => $notificacion->token,
                'email' => $usuario->email,
                'password' => 'NuevaClave9',
                'password_confirmation' => 'NuevaClave9',
            ])->assertRedirect(route('login'))->assertSessionHasNoErrors();

            return Hash::check('NuevaClave9', $usuario->fresh()->password);
        });
    }

    public function test_exige_contrasenia_segura(): void
    {
        Notification::fake();
        $usuario = $this->crearUsuario(RolSistema::Preceptor);
        $this->post(route('password.email'), ['email' => $usuario->email]);

        Notification::assertSentTo($usuario, RestablecerContrasenia::class, function (RestablecerContrasenia $notificacion) use ($usuario) {
            $this->post(route('password.update'), [
                'token' => $notificacion->token,
                'email' => $usuario->email,
                'password' => 'debil',
                'password_confirmation' => 'debil',
            ])->assertSessionHasErrors(['password' => ContraseniaSegura::MENSAJE]);

            return true;
        });
    }
}
