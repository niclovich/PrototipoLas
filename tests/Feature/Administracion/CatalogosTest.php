<?php

namespace Tests\Feature\Administracion;

use App\Enums\RolSistema;
use App\Models\Localidad;
use App\Models\Modalidad;
use App\Models\Provincia;
use App\Support\DefinicionCatalogo;
use Database\Seeders\CatalogosSeeder;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

class CatalogosTest extends TestCase
{
    use ConRolesYPermisos;

    public function test_listado_y_pantalla_de_cada_catalogo(): void
    {
        $admin = $this->superAdmin();
        $this->seed(CatalogosSeeder::class);

        $listado = $this->actingAs($admin)->get(route('panel.catalogos.index'))->assertOk();

        foreach (DefinicionCatalogo::todos() as $catalogo) {
            $listado->assertSee($catalogo->titulo);
            $this->actingAs($admin)->get(route('panel.catalogos.registros.index', $catalogo->clave))
                ->assertOk()->assertSee('Activos')->assertSee('Eliminados');
            $this->actingAs($admin)->get(route('panel.catalogos.registros.create', $catalogo->clave))->assertOk();
            $this->actingAs($admin)->postJson(route('panel.catalogos.registros.datatable', $catalogo->clave), ['draw' => 1, 'start' => 0, 'length' => 10])
                ->assertOk()->assertJsonStructure(['data']);
        }
    }

    public function test_catalogo_inexistente_devuelve_404(): void
    {
        $this->actingAs($this->superAdmin())->get('/panel/administracion/catalogos/no-existe')->assertNotFound();
    }

    public function test_alta_modificacion_baja_y_reactivacion(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('panel.catalogos.registros.store', 'modalidades'), ['modalidad' => 'Virtual'])
            ->assertRedirect(route('panel.catalogos.registros.index', 'modalidades'));
        $modalidad = Modalidad::where('modalidad', 'Virtual')->firstOrFail();

        $this->actingAs($admin)->get(route('panel.catalogos.registros.edit', ['modalidades', $modalidad->id]))->assertOk()->assertSee('Virtual');
        $this->actingAs($admin)->put(route('panel.catalogos.registros.update', ['modalidades', $modalidad->id]), ['modalidad' => 'Virtual sincrónica'])
            ->assertSessionHasNoErrors();
        $this->assertSame('Virtual sincrónica', $modalidad->fresh()->modalidad);

        $this->actingAs($admin)->delete(route('panel.catalogos.registros.destroy', ['modalidades', $modalidad->id]))->assertSessionHas('success');
        $this->assertSoftDeleted($modalidad);

        $eliminados = $this->actingAs($admin)->postJson(route('panel.catalogos.registros.datatable', 'modalidades'), ['draw' => 1, 'start' => 0, 'length' => 10, 'pestania' => 'eliminados'])
            ->json('data');
        $this->assertSame(['Virtual sincrónica'], array_column($eliminados, 'modalidad'));

        $this->actingAs($admin)->patch(route('panel.catalogos.registros.reactivar', ['modalidades', $modalidad->id]))->assertSessionHas('success');
        $this->assertNotSoftDeleted($modalidad);
    }

    public function test_validaciones_generadas_desde_la_configuracion(): void
    {
        $admin = $this->superAdmin();
        $salta = Provincia::factory()->create(['provincia' => 'Salta']);
        Localidad::factory()->create(['provincia_id' => $salta->id, 'localidad' => 'Cafayate']);
        $jujuy = Provincia::factory()->create(['provincia' => 'Jujuy']);

        $this->actingAs($admin)->post(route('panel.catalogos.registros.store', 'localidades'), ['provincia_id' => $salta->id, 'localidad' => 'Cafayate'])
            ->assertSessionHasErrors(['localidad' => 'Ya existe un registro con ese valor (revise también la pestaña Eliminados).']);

        // La unicidad es por provincia: el mismo nombre en otra provincia es válido.
        $this->actingAs($admin)->post(route('panel.catalogos.registros.store', 'localidades'), ['provincia_id' => $jujuy->id, 'localidad' => 'Cafayate'])
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)->post(route('panel.catalogos.registros.store', 'duraciones'), ['duracion' => '', 'cantidad_anios' => 20])
            ->assertSessionHasErrors([
                'duracion' => 'El campo duración es obligatorio.',
                'cantidad_anios' => 'El campo cantidad de años no debe ser mayor que 10.',
            ]);
    }

    public function test_un_preceptor_no_accede_a_catalogos(): void
    {
        $this->actingAs($this->crearUsuario(RolSistema::Preceptor))
            ->get(route('panel.catalogos.registros.index', 'modalidades'))->assertForbidden();
    }
}
