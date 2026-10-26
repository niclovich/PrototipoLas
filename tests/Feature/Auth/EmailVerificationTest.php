<?php

namespace Tests\Feature\Auth;

use App\Enums\RolSistema;
use App\Notifications\VerificarCorreo;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use ConRolesYPermisos;

    public function test_usuario_sin_verificar_no_entra_al_panel(): void
    {
        $usuario = $this->crearUsuario(RolSistema::Preceptor, ['email_verified_at' => null]);

        $this->actingAs($usuario)->get(route('panel.inicio'))->assertRedirect(route('verification.notice'));
        $this->actingAs($usuario)->get(route('verification.notice'))->assertOk()->assertSee('Verificar correo electrónico');
    }

    public function test_reenvia_notificacion_propia_encolada_y_en_espanol(): void
    {
        Notification::fake();
        $usuario = $this->crearUsuario(RolSistema::Preceptor, ['email_verified_at' => null]);

        $this->actingAs($usuario)->post(route('verification.send'))->assertSessionHas('status', 'verification-link-sent');

        Notification::assertSentTo($usuario, VerificarCorreo::class, function (VerificarCorreo $notificacion) use ($usuario) {
            return $notificacion instanceof ShouldQueue
                && str_starts_with($notificacion->toMail($usuario)->subject, 'Verificar correo electrónico');
        });
    }

    public function test_el_link_firmado_verifica_el_correo(): void
    {
        $usuario = $this->crearUsuario(RolSistema::Preceptor, ['email_verified_at' => null]);
        $url = URL::temporarySignedRoute('verification.verify', now()->addHour(), ['id' => $usuario->id, 'hash' => sha1($usuario->email)]);

        $this->actingAs($usuario)->get($url)->assertRedirect('/panel?verified=1');

        $this->assertTrue($usuario->fresh()->hasVerifiedEmail());
    }
}
