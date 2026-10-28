<?php

namespace Tests\Feature\Alumnos;

use App\Enums\EstadoAlumno;
use App\Enums\RolSistema;
use App\Models\Alumno;
use App\Models\User;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

/** P1 Gestión de Alumnos (entrada de CU7): pestañas Activos / Preinscriptos, filtro y fila expandible. */
class GestionAlumnosTest extends TestCase
{
    use ConRolesYPermisos;

    private function pedir(array $datos): array
    {
        return $this->postJson(route('panel.alumnos.datatable'), ['draw' => 1, 'start' => 0, 'length' => 10, ...$datos])->assertOk()->json('data');
    }

    public function test_gestion_de_alumnos_lista_y_filtra(): void
    {
        $inscripto = Alumno::factory()->create(['estado_alumno' => EstadoAlumno::Inscripto, 'nro_legajo' => 12458]);
        $inscripto->usuario->update(['apellido_usuario' => 'Muñoz']);
        $preinscripto = Alumno::factory()->create();

        $this->actingAs($this->crearUsuario(RolSistema::Cajero));
        $this->get(route('panel.alumnos.index'))->assertOk()->assertSee('Preinscriptos')->assertSee('Registrar Alumno');

        $activos = $this->pedir(['pestania' => 'activos']);
        $this->assertCount(1, $activos);
        $this->assertStringContainsString('12458', $activos[0]['acciones']);
        $this->assertStringContainsString(route('panel.pagos.create', $inscripto), $activos[0]['acciones']);

        $this->assertSame($preinscripto->usuario->dni_usuario, $this->pedir(['pestania' => 'preinscriptos'])[0]['dni_usuario']);
        $this->assertCount(0, $this->pedir(['pestania' => 'activos', 'buscar' => 'NoExiste']));
    }

    public function test_el_alumno_ve_solo_lo_suyo(): void
    {
        $propio = Alumno::factory()->create(['estado_alumno' => EstadoAlumno::Inscripto]);
        Alumno::factory()->create(['estado_alumno' => EstadoAlumno::Inscripto]);
        /** @var User $usuario */
        $usuario = $propio->usuario;
        $usuario->assignRole(RolSistema::Alumno->value);

        $this->actingAs($usuario);
        $filas = $this->pedir(['pestania' => 'activos']);
        $this->assertCount(1, $filas);
        $this->assertSame($usuario->dni_usuario, $filas[0]['dni_usuario']);
    }
}
