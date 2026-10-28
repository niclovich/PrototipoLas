<?php

namespace Tests\Feature\Academico;

use App\Enums\EstadoCarrera;
use App\Enums\RolSistema;
use App\Models\Carrera;
use App\Models\Duracion;
use App\Models\Modalidad;
use App\Models\User;
use App\Rules\ResolucionMinisterial;
use App\Services\Academico\CarreraService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

/** CU 3 Registrar datos de una nueva Carrera. */
class RegistrarCarreraTest extends TestCase
{
    use ConRolesYPermisos;

    private User $coordinador;

    private Modalidad $modalidad;

    private Duracion $duracion;

    protected function setUp(): void
    {
        parent::setUp();
        $this->coordinador = $this->crearUsuario(RolSistema::Coordinador);
        $this->modalidad = Modalidad::factory()->create(['modalidad' => 'Presencial']);
        $this->duracion = Duracion::factory()->create(['duracion' => '5 años', 'cantidad_anios' => 5]);
    }

    private function datos(array $cambios = []): array
    {
        return [
            'nombre_carrera' => 'Licenciatura en Análisis de Sistemas',
            'modalidad_id' => $this->modalidad->id,
            'duracion_id' => $this->duracion->id,
            'resolucion_carrera' => 'Resolución N° 1234/2020',
            ...$cambios,
        ];
    }

    private function registrar(array $cambios = [])
    {
        return $this->actingAs($this->coordinador)->from(route('panel.carreras.create'))->post(route('panel.carreras.store'), $this->datos($cambios));
    }

    public function test_coordinador_ve_el_formulario(): void
    {
        $this->get(route('panel.carreras.create'))->assertRedirect(route('login'));
        $this->actingAs($this->crearUsuario(RolSistema::Preceptor))->get(route('panel.carreras.create'))->assertForbidden();

        $this->actingAs($this->coordinador)->get(route('panel.carreras.create'))->assertOk()
            ->assertSeeInOrder(['GESTIÓN DE CARRERAS', 'Registrar Carrera', 'Datos de la Carrera', 'Nombre de la Carrera',
                'Ingrese el nombre de la carrera', 'Modalidad', 'Seleccione una modalidad', 'Duración', 'Seleccione una duración',
                'Resolución Ministerial', 'Ej: Resolución N° 1234/2020', 'Cancelar', 'Registrar Carrera'])
            ->assertDontSee('name="estado_carrera"', false);
    }

    public function test_registra_carrera_pendiente(): void
    {
        $this->registrar(['estado_carrera' => 'Activo'])->assertRedirect(route('panel.carreras.confirmacion'));

        $carrera = Carrera::sole();
        $this->assertSame('Licenciatura en Análisis de Sistemas', $carrera->nombre_carrera);
        $this->assertSame('1234/2020', $carrera->resolucion_carrera);
        $this->assertSame(EstadoCarrera::Pendiente, $carrera->estado_carrera, 'El estado lo fija el servidor.');
        $this->assertSame($this->modalidad->id, $carrera->modalidad_id);
        $this->assertSame($this->duracion->id, $carrera->duracion_id);

        $this->followRedirects($this->registrar(['nombre_carrera' => 'Tecnicatura en Redes', 'resolucion_carrera' => '5678/2021']))
            ->assertSeeInOrder([CarreraService::CONFIRMACION_REGISTRO, 'Carrera: Tecnicatura en Redes',
                'Modalidad: Presencial · Duración: 5 años', 'Estado: Pendiente', 'Aceptar'])
            ->assertSee('data-modal-confirmacion', false)
            ->assertSee('aria-modal="true"', false)
            ->assertSee('data-backdrop="static"', false);
        $this->assertSame('Carrera registrada exitosamente', CarreraService::CONFIRMACION_REGISTRO);
    }

    public function test_confirmacion_sin_datos_redirige_a_p1(): void
    {
        $this->actingAs($this->coordinador)->get(route('panel.carreras.confirmacion'))->assertRedirect(route('panel.carreras.index'));
    }

    public function test_no_registra_carrera_existente(): void
    {
        Carrera::factory()->create(['nombre_carrera' => 'Licenciatura en Análisis de Sistemas']);

        $this->registrar(['nombre_carrera' => '  licenciatura   EN análisis de sistemas '])
            ->assertSessionHasErrors(['nombre_carrera' => CarreraService::MENSAJE_CARRERA_EXISTENTE]);
        $this->assertSame('Carrera ya existente', CarreraService::MENSAJE_CARRERA_EXISTENTE);

        $this->followRedirects($this->registrar())
            ->assertSee('Carrera ya existente')->assertSee('data-abrir', false)->assertSee('data-foco="#nombre_carrera"', false);
        $this->assertSame(1, Carrera::count());
    }

    public function test_traduce_la_unicidad_concurrente_al_registrar(): void
    {
        Carrera::creating(function () {
            throw new UniqueConstraintViolationException(
                'mysql',
                'insert into `carreras` (`nombre_carrera`) values (?)',
                [],
                new \Exception("SQLSTATE[23000]: Duplicate entry 'x' for key 'carreras_nombre_carrera_unique'"),
            );
        });

        try {
            app(CarreraService::class)->registrar($this->datos());
            $this->fail('Debía traducir la colisión concurrente.');
        } catch (ValidationException $error) {
            $this->assertSame(['nombre_carrera' => [CarreraService::MENSAJE_CARRERA_EXISTENTE]], $error->errors());
        }

        $this->assertSame(0, Carrera::count());
    }

    /** @return array<string, array{string}> */
    public static function resolucionesInvalidas(): array
    {
        return ['guion' => ['1234-2020'], 'número largo' => ['123456/2020'], 'año corto' => ['1234/20'], 'prefijo distinto' => ['Res 1234/2020'], 'texto' => ['abc']];
    }

    #[DataProvider('resolucionesInvalidas')]
    public function test_resolucion_con_formato_incorrecto(string $resolucion): void
    {
        $this->registrar(['resolucion_carrera' => $resolucion])->assertSessionHasErrors(['resolucion_carrera' => ResolucionMinisterial::MENSAJE]);
        $this->assertSame('La resolución ministerial no tiene el formato correcto', ResolucionMinisterial::MENSAJE);
        $this->followRedirects($this->registrar(['resolucion_carrera' => $resolucion]))->assertSee('Resolución inválida');
    }

    /** @return array<string, array{string}> */
    public static function resolucionesValidas(): array
    {
        return ['solo número' => ['1234/2020'], 'con prefijo' => ['Resolución N° 1234/2020'], 'sin tildes' => ['Resolucion N 1234/2020']];
    }

    #[DataProvider('resolucionesValidas')]
    public function test_resolucion_con_formato_correcto(string $resolucion): void
    {
        $this->registrar(['resolucion_carrera' => $resolucion])->assertSessionHasNoErrors();
        $this->assertSame('1234/2020', Carrera::sole()->resolucion_carrera);
    }

    public function test_nombre_antes_que_resolucion_y_obligatorios(): void
    {
        Carrera::factory()->create(['nombre_carrera' => 'Licenciatura en Análisis de Sistemas']);

        $this->followRedirects($this->registrar(['resolucion_carrera' => 'abc']))
            ->assertSee('data-foco="#nombre_carrera"', false)->assertSee(ResolucionMinisterial::MENSAJE);

        $this->actingAs($this->coordinador)->post(route('panel.carreras.store'), [])->assertSessionHasErrors([
            'nombre_carrera' => 'Debe ingresar el nombre de la carrera.',
            'modalidad_id' => 'Debe seleccionar una modalidad.',
            'duracion_id' => 'Debe seleccionar una duración.',
            'resolucion_carrera' => 'Debe ingresar la resolución ministerial.',
        ]);
    }

    public function test_rechaza_modalidad_o_duracion_eliminada_o_inexistente(): void
    {
        $this->modalidad->delete();

        $this->registrar(['duracion_id' => 999999])->assertSessionHasErrors(['modalidad_id', 'duracion_id']);
        $this->assertSame(0, Carrera::count());
    }
}
