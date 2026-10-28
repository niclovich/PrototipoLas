<?php

namespace Tests\Feature\Catalogos;

use App\Enums\RolSistema;
use App\Models\Localidad;
use App\Models\Provincia;
use App\Models\User;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

/** Localidades de una Provincia para los selects dependientes (ruta de Personal). */
class LocalidadesPorProvinciaTest extends TestCase
{
    use ConRolesYPermisos;

    public function test_devuelve_las_localidades_ordenadas_y_sin_eliminadas(): void
    {
        $director = User::factory()->personal(RolSistema::DirectorGeneral)->create();
        $salta = Provincia::factory()->create();
        $b = Localidad::factory()->create(['provincia_id' => $salta->id, 'localidad' => 'Cafayate']);
        $a = Localidad::factory()->create(['provincia_id' => $salta->id, 'localidad' => 'Cachi']);
        Localidad::factory()->create(['provincia_id' => $salta->id, 'localidad' => 'Eliminada'])->delete();
        Localidad::factory()->create(['localidad' => 'De otra provincia']);

        $this->actingAs($director)->getJson(route('panel.personal.localidades', $salta))
            ->assertOk()
            ->assertExactJson([['id' => $a->id, 'localidad' => 'Cachi'], ['id' => $b->id, 'localidad' => 'Cafayate']]);
    }

    public function test_provincia_inexistente_404(): void
    {
        $this->actingAs(User::factory()->personal(RolSistema::DirectorGeneral)->create())
            ->getJson(route('panel.personal.localidades', 999999))->assertNotFound();
    }
}
