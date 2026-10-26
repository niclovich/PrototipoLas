<?php

namespace Tests\Feature\Administracion;

use App\Enums\EstadoCuenta;
use App\Enums\RolSistema;
use App\Models\User;
use App\Notifications\DefinirContrasenia;
use App\Services\Usuarios\UsuarioService;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

class UsuariosTest extends TestCase
{
    use ConRolesYPermisos;

    private function datos(array $cambios = []): array
    {
        return [
            'dni_usuario' => '30.111.222',
            'apellido_usuario' => 'Gómez',
            'nombre_usuario' => 'Luis',
            'email' => 'Luis@LAS.test',
            'rol' => RolSistema::Preceptor->value,
            ...$cambios,
        ];
    }

    public function test_index_con_pestanias_y_datatable(): void
    {
        $admin = $this->superAdmin();
        $this->crearUsuario(RolSistema::Cajero, ['apellido_usuario' => 'Zapata']);
        $this->crearUsuario(RolSistema::Docente, ['apellido_usuario' => 'Inactivo', 'estado_cuenta' => EstadoCuenta::Inactivo]);

        $this->actingAs($admin)->get(route('panel.usuarios.index'))
            ->assertOk()->assertSee('Activos')->assertSee('Eliminados')->assertSee('Nuevo usuario');

        $activos = $this->actingAs($admin)->postJson(route('panel.usuarios.datatable'), ['draw' => 1, 'start' => 0, 'length' => 10, 'pestania' => 'activos'])
            ->assertOk()->json('data');
        $this->assertContains('Zapata', array_map(fn ($f) => explode(',', $f['apellido_nombre'])[0], $activos));
        $this->assertNotContains('Inactivo', array_map(fn ($f) => explode(',', $f['apellido_nombre'])[0], $activos));

        $eliminados = $this->actingAs($admin)->postJson(route('panel.usuarios.datatable'), ['draw' => 1, 'start' => 0, 'length' => 10, 'pestania' => 'eliminados'])
            ->assertOk()->json('data');
        $this->assertCount(1, $eliminados);
        $this->assertStringContainsString('Reactivar', $eliminados[0]['acciones']);
    }

    public function test_alta_con_link_firmado_y_un_rol(): void
    {
        Notification::fake();
        $admin = $this->superAdmin();

        $this->actingAs($admin)->get(route('panel.usuarios.create'))->assertOk()->assertSee('Correo electrónico');
        $this->actingAs($admin)->post(route('panel.usuarios.store'), $this->datos())
            ->assertRedirect(route('panel.usuarios.index'))->assertSessionHas('success');

        $usuario = User::where('email', 'luis@las.test')->firstOrFail();
        $this->assertSame('30111222', $usuario->dni_usuario);
        $this->assertNull($usuario->password);
        $this->assertSame([RolSistema::Preceptor->value], $usuario->getRoleNames()->all());
        Notification::assertSentTo($usuario, DefinirContrasenia::class);
    }

    public function test_validaciones_del_alta(): void
    {
        $admin = $this->superAdmin();
        $this->crearUsuario(RolSistema::Cajero, ['dni_usuario' => '30111222']);

        $this->actingAs($admin)->post(route('panel.usuarios.store'), $this->datos(['rol' => RolSistema::Alumno->value, 'email' => 'x']))
            ->assertSessionHasErrors([
                'dni_usuario' => 'Ya existe un usuario con ese DNI.',
                'email' => 'El campo correo electrónico debe ser un correo electrónico válido.',
                'rol' => 'Los usuarios Alumno se registran desde la Preinscripción.',
            ]);
    }

    public function test_modificar_cambia_datos_y_rol_con_sync_roles(): void
    {
        $admin = $this->superAdmin();
        $usuario = $this->crearUsuario(RolSistema::Cajero);

        $this->actingAs($admin)->get(route('panel.usuarios.edit', $usuario))->assertOk()->assertSee($usuario->apellido_usuario);
        $this->actingAs($admin)->put(route('panel.usuarios.update', $usuario), $this->datos(['email' => $usuario->email]))
            ->assertRedirect(route('panel.usuarios.index'));

        $usuario->refresh();
        $this->assertSame('Gómez', $usuario->apellido_usuario);
        $this->assertSame([RolSistema::Preceptor->value], $usuario->getRoleNames()->all());
        $this->assertTrue($usuario->hasVerifiedEmail());
    }

    public function test_baja_y_reactivacion(): void
    {
        $admin = $this->superAdmin();
        $usuario = $this->crearUsuario(RolSistema::Cajero);

        $this->actingAs($admin)->delete(route('panel.usuarios.destroy', $usuario))->assertSessionHas('success');
        $this->assertSame(EstadoCuenta::Inactivo, $usuario->fresh()->estado_cuenta);

        $this->actingAs($admin)->patch(route('panel.usuarios.reactivar', $usuario))->assertSessionHas('success');
        $this->assertSame(EstadoCuenta::Activo, $usuario->fresh()->estado_cuenta);
    }

    public function test_no_puede_darse_de_baja_a_si_mismo(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->delete(route('panel.usuarios.destroy', $admin))
            ->assertSessionHasErrors(['accion' => 'No puede dar de baja su propio usuario.']);
        $this->assertSame(EstadoCuenta::Activo, $admin->fresh()->estado_cuenta);
    }

    public function test_reenviar_link_solo_si_no_definio_contrasenia(): void
    {
        Notification::fake();
        $admin = $this->superAdmin();
        $pendiente = User::factory()->sinContrasenia()->create();
        $conClave = $this->crearUsuario(RolSistema::Cajero);

        $this->actingAs($admin)->post(route('panel.usuarios.reenviar-link', $pendiente))->assertSessionHas('success');
        $this->actingAs($admin)->post(route('panel.usuarios.reenviar-link', $conClave))->assertSessionHas('warning');

        Notification::assertSentTo($pendiente, DefinirContrasenia::class);
        Notification::assertNotSentTo($conClave, DefinirContrasenia::class);
    }

    public function test_solo_un_superadmin_asigna_el_rol_superadmin(): void
    {
        $director = $this->crearUsuario(RolSistema::DirectorGeneral);
        $director->givePermissionTo(['crear_usuario', 'editar_usuario', 'eliminar_usuario']);

        $this->actingAs($director)->get(route('panel.usuarios.create'))->assertOk()->assertDontSee('value="SUPERADMIN"', false);
        $this->actingAs($director)->post(route('panel.usuarios.store'), $this->datos(['rol' => RolSistema::SuperAdmin->value]))
            ->assertSessionHasErrors(['rol' => UsuarioService::MENSAJE_SOLO_SUPERADMIN]);
        $this->assertDatabaseMissing('users', ['email' => 'luis@las.test']);

        $otroAdmin = $this->superAdmin();
        $this->actingAs($director)->delete(route('panel.usuarios.destroy', $otroAdmin))
            ->assertSessionHasErrors(['accion' => UsuarioService::MENSAJE_SOLO_SUPERADMIN]);
        $this->assertTrue($otroAdmin->fresh()->estaActivo());

        $this->actingAs($this->superAdmin())->post(route('panel.usuarios.store'), $this->datos(['rol' => RolSistema::SuperAdmin->value]))
            ->assertSessionHasNoErrors();
    }

    public function test_no_puede_cambiar_su_propio_rol(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->put(route('panel.usuarios.update', $admin), $this->datos([
            'email' => $admin->email, 'dni_usuario' => $admin->dni_usuario, 'rol' => RolSistema::Cajero->value,
        ]))->assertSessionHasErrors(['rol' => 'No puede cambiar su propio rol.']);

        $this->assertTrue($admin->fresh()->hasRole(RolSistema::SuperAdmin->value));
    }

    public function test_los_usuarios_alumno_no_se_gestionan_desde_usuarios(): void
    {
        $admin = $this->superAdmin();
        $alumno = $this->crearUsuario(RolSistema::Alumno, ['apellido_usuario' => 'Alumnez']);

        $filas = $this->actingAs($admin)->postJson(route('panel.usuarios.datatable'), ['draw' => 1, 'start' => 0, 'length' => 50, 'pestania' => 'activos'])
            ->json('data');
        $this->assertNotContains($alumno->id, array_column($filas, 'id'));

        $this->actingAs($admin)->get(route('panel.usuarios.edit', $alumno->id))->assertNotFound();
        $this->actingAs($admin)->put(route('panel.usuarios.update', $alumno->id), $this->datos())->assertNotFound();
        $this->assertTrue($alumno->fresh()->hasRole(RolSistema::Alumno->value));
    }

    public function test_baja_corta_sesiones_y_recordarme(): void
    {
        $admin = $this->superAdmin();
        $usuario = $this->crearUsuario(RolSistema::Cajero, ['remember_token' => 'token-viejo']);

        $this->actingAs($admin)->delete(route('panel.usuarios.destroy', $usuario));

        $this->assertNotSame('token-viejo', $usuario->fresh()->remember_token);
    }

    public function test_los_cambios_quedan_auditados(): void
    {
        $admin = $this->superAdmin();
        $usuario = $this->crearUsuario(RolSistema::Cajero);

        $this->actingAs($admin)->delete(route('panel.usuarios.destroy', $usuario));

        $this->assertDatabaseHas('activity_log', [
            'subject_id' => $usuario->id,
            'subject_type' => User::class,
            'causer_id' => $admin->id,
            'event' => 'updated',
        ]);
    }
}
