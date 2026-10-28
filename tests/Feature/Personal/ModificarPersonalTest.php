<?php

namespace Tests\Feature\Personal;

use App\Enums\RolSistema;
use App\Models\Localidad;
use App\Models\Provincia;
use App\Models\Rol;
use App\Models\User;
use App\Notifications\VerificarCorreo;
use App\Services\Personal\PersonalService;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

/** CU 26 Modificar datos del Personal. */
class ModificarPersonalTest extends TestCase
{
    use ConRolesYPermisos;

    private User $director;

    private User $docente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->director = User::factory()->personal(RolSistema::DirectorGeneral)->conDomicilio()->create();
        $this->docente = User::factory()->personal(RolSistema::Docente)->conDomicilio()->create(['dni_usuario' => '30125478', 'email' => 'docente@instituto.edu']);
    }

    private function datos(array $cambios = []): array
    {
        $localidad = $this->docente->localidad;

        return [
            'apellido_usuario' => 'Gonzalez',
            'nombre_usuario' => 'Maria José',
            'telefono_usuario' => '387 4567890',
            'email' => 'docente@instituto.edu',
            'direccion_usuario' => 'Caseros 456',
            'provincia_id' => $localidad->provincia_id,
            'localidad_id' => $localidad->id,
            'rol_id' => Rol::findByName(RolSistema::Docente->value)->id,
            ...$cambios,
        ];
    }

    private function modificar(array $cambios = [], ?User $usuario = null)
    {
        return $this->actingAs($this->director)->from(route('panel.personal.edit', $usuario ?? $this->docente))
            ->put(route('panel.personal.update', $usuario ?? $this->docente), $this->datos($cambios));
    }

    public function test_muestra_datos_actuales(): void
    {
        $this->actingAs($this->director)->get(route('panel.personal.edit', $this->docente))->assertOk()
            ->assertSeeInOrder(['GESTIÓN DE PERSONAL', 'Modificar Datos del Personal', '1 · DATOS PERSONALES', 'DNI', 'Apellido', 'Nombre',
                'Teléfono', 'Correo', 'Dirección', 'Provincia', 'Localidad', '2 · ROL', 'Rol', 'Descripción del Rol', 'Cancelar', 'Guardar Cambios'])
            ->assertSee('value="30125478"', false)
            ->assertSee('readonly', false)
            ->assertDontSee('ROL Y CUENTA DE ACCESO')
            ->assertDontSee('name="password"', false)
            ->assertSee($this->docente->localidad->localidad);
    }

    public function test_modifica_personal_localidad_y_rol(): void
    {
        $nueva = Localidad::factory()->create();

        $this->modificar(['provincia_id' => $nueva->provincia_id, 'localidad_id' => $nueva->id, 'rol_id' => Rol::findByName('Preceptor')->id])
            ->assertRedirect(route('panel.personal.confirmacion'))
            ->assertSessionHas('confirmacion', PersonalService::CONFIRMACION_MODIFICACION);

        $this->docente->refresh();
        $this->assertSame('Maria José', $this->docente->nombre_usuario);
        $this->assertSame($nueva->id, $this->docente->localidad_id);
        $this->assertSame(['Preceptor'], $this->docente->getRoleNames()->all());
    }

    public function test_no_cambia_el_dni_aunque_se_envie(): void
    {
        $this->modificar(['dni_usuario' => '11111111']);

        $this->assertSame('30125478', $this->docente->fresh()->dni_usuario);
    }

    public function test_cambio_de_correo_pide_verificar(): void
    {
        Notification::fake();

        $this->modificar(['email' => 'nuevo@instituto.edu']);

        $this->assertNull($this->docente->fresh()->email_verified_at);
        Notification::assertSentTo($this->docente, VerificarCorreo::class);
    }

    /** @return array<string, array{array<string, mixed>, string}> */
    public static function datosInvalidos(): array
    {
        return [
            'apellido vacío' => [['apellido_usuario' => ''], 'apellido_usuario'],
            'correo mal formado' => [['email' => 'sin-arroba'], 'email'],
            'teléfono con letras' => [['telefono_usuario' => 'tel. abc'], 'telefono_usuario'],
            'localidad de otra provincia' => [['provincia_id' => 'otra'], 'localidad_id'],
        ];
    }

    #[DataProvider('datosInvalidos')]
    public function test_datos_invalidos(array $cambios, string $campo): void
    {
        if (($cambios['provincia_id'] ?? null) === 'otra') {
            $cambios['provincia_id'] = Provincia::factory()->create()->id;
        }

        $this->modificar($cambios)->assertSessionHasErrors($campo);

        $this->followRedirects($this->modificar($cambios))
            ->assertSee('Datos inválidos')->assertSee(PersonalService::MENSAJE_DATOS_INVALIDOS)->assertSee('data-abrir', false);
        $this->assertSame('Debe completar los datos obligatorios con un formato válido', PersonalService::MENSAJE_DATOS_INVALIDOS);
        $this->assertNotSame('Maria José', $this->docente->fresh()->nombre_usuario);
    }

    public function test_cancelar_no_modifica(): void
    {
        $antes = $this->docente->fresh()->toArray();

        $this->actingAs($this->director)->get(route('panel.personal.edit', $this->docente))->assertSee('href="'.route('panel.personal.index').'"', false);
        $this->actingAs($this->director)->get(route('panel.personal.index'))->assertOk();

        $this->assertSame($antes, $this->docente->fresh()->toArray());
    }

    public function test_no_modifica_inactivo_alumno_ni_superadmin(): void
    {
        foreach ([User::factory()->personal()->inactivo()->create(), $this->crearUsuario(RolSistema::Alumno), $this->superAdmin()] as $usuario) {
            $this->actingAs($this->director)->get(route('panel.personal.edit', $usuario->id))->assertNotFound();
        }
    }

    public function test_no_puede_cambiar_su_propio_rol(): void
    {
        $localidad = $this->director->localidad;

        $this->actingAs($this->director)->put(route('panel.personal.update', $this->director), [
            ...$this->datos(['email' => $this->director->email, 'provincia_id' => $localidad->provincia_id, 'localidad_id' => $localidad->id]),
            'rol_id' => Rol::findByName('Cajero')->id,
        ])->assertSessionHasErrors(['rol_id' => 'No puede cambiar su propio rol.']);

        // El motivo se ve debajo del campo Rol (además del modal "Datos inválidos").
        $this->followRedirects($this->actingAs($this->director)->from(route('panel.personal.edit', $this->director))
            ->put(route('panel.personal.update', $this->director), [
                ...$this->datos(['email' => $this->director->email, 'provincia_id' => $localidad->provincia_id, 'localidad_id' => $localidad->id]),
                'rol_id' => Rol::findByName('Cajero')->id,
            ]))->assertSee('No puede cambiar su propio rol.')->assertSee('Datos inválidos');

        $this->assertTrue($this->director->fresh()->hasRole(RolSistema::DirectorGeneral->value));
    }
}
