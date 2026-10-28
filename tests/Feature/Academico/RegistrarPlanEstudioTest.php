<?php

namespace Tests\Feature\Academico;

use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Models\Carrera;
use App\Models\PlanEstudio;
use App\Models\User;
use App\Services\Academico\PlanEstudioService;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

/** CU 5 Registrar Plan de Estudio. */
class RegistrarPlanEstudioTest extends TestCase
{
    use ConRolesYPermisos;

    private User $coordinador;

    private Carrera $carrera;

    protected function setUp(): void
    {
        parent::setUp();
        $this->coordinador = $this->crearUsuario(RolSistema::Coordinador);
        $this->carrera = Carrera::factory()->pendiente()->create(['nombre_carrera' => 'Tecnicatura en Redes']);
    }

    private function registrar(mixed $anio)
    {
        return $this->actingAs($this->coordinador)->from(route('panel.planes.create', $this->carrera))
            ->post(route('panel.planes.store', $this->carrera), ['anio_plan_estudio' => $anio, 'estado_plan_estudio' => 'Inactivo']);
    }

    public function test_registra_plan_activo_y_muestra_la_confirmacion(): void
    {
        $this->actingAs($this->coordinador)->get(route('panel.planes.create', $this->carrera))->assertOk()
            ->assertSeeInOrder(['Registrar Plan de Estudio', '1 · Datos del Plan', 'Carrera', 'Tecnicatura en Redes',
                'Año del Plan de Estudio', 'Registrar Plan de Estudio', 'Cancelar']);

        $this->followRedirects($this->registrar(2026))->assertSeeInOrder([
            'Plan de estudio registrado exitosamente', 'Carrera: Tecnicatura en Redes', 'Año del Plan de Estudio: 2026', 'Aceptar',
        ]);

        $plan = PlanEstudio::sole();
        $this->assertSame([$this->carrera->id, 2026, EstadoGeneral::Activo], [$plan->carrera_id, $plan->anio_plan_estudio, $plan->estado_plan_estudio]);

        // Ya tiene plan activo: no se registra un segundo (E6-D9).
        $this->registrar(2025)->assertRedirect(route('panel.planes.index', ['carrera' => $this->carrera->id]));
        $this->actingAs($this->coordinador)->get(route('panel.planes.create', $this->carrera))->assertRedirect();
        $this->assertSame(1, PlanEstudio::count());
    }

    public function test_alt_7_anio_invalido(): void
    {
        $this->assertSame('El año ingresado no es válido', PlanEstudioService::MENSAJE_ANIO_INVALIDO);
        PlanEstudio::factory()->inactivo()->create(['carrera_id' => $this->carrera->id, 'anio_plan_estudio' => 2020]);

        foreach (['', 'abc', 1989, now()->year + 2, '2026.5', 2020] as $anio) {
            $this->registrar($anio)->assertSessionHasErrors(['anio_plan_estudio' => PlanEstudioService::MENSAJE_ANIO_INVALIDO]);
        }
        $this->followRedirects($this->registrar(1989))->assertSee('Año inválido')->assertSee(PlanEstudioService::MENSAJE_ANIO_INVALIDO);
        $this->assertSame(1, PlanEstudio::count());
    }
}
