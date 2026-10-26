<?php

namespace Tests\Feature\Autorizacion;

use App\Enums\RolSistema;
use App\Models\Modalidad;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Routing\Route as RutaLaravel;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

/**
 * Todas las rutas panel.*, con su verbo real y sus parámetros creados con factories:
 * invitado → login; sin permiso → 403; con permiso → 200/302; SUPERADMIN → 200/302.
 */
class RutasPanelTest extends TestCase
{
    use ConRolesYPermisos;

    /** @return list<RutaLaravel> */
    private function rutasPanel(): array
    {
        return collect(Route::getRoutes()->getRoutes())
            ->filter(fn (RutaLaravel $r) => Str::startsWith((string) $r->getName(), 'panel.'))
            ->values()
            ->all();
    }

    private function permiso(RutaLaravel $ruta): ?string
    {
        $can = collect($ruta->gatherMiddleware())->first(fn ($m) => is_string($m) && str_starts_with($m, 'can:'));

        return $can ? Str::after($can, 'can:') : null;
    }

    private function verbo(RutaLaravel $ruta): string
    {
        return collect($ruta->methods())->reject(fn ($m) => $m === 'HEAD')->first();
    }

    /** Parámetros nuevos en cada pedido (una baja no afecta al siguiente). */
    private function url(RutaLaravel $ruta): string
    {
        $parametros = collect($ruta->parameterNames())->mapWithKeys(fn (string $nombre) => [$nombre => match ($nombre) {
            'usuario' => User::factory()->sinContrasenia()->create()->assignRole(RolSistema::Cajero->value)->id,
            'rol' => Rol::create(['name' => 'Rol '.Str::random(6), 'guard_name' => 'web'])->id,
            'catalogo' => 'modalidades',
            'registro' => Modalidad::factory()->create()->id,
        }]);

        return route($ruta->getName(), $parametros->all());
    }

    private function pedir(RutaLaravel $ruta)
    {
        return $this->call($this->verbo($ruta), $this->url($ruta));
    }

    public function test_hay_un_dataset_completo(): void
    {
        $nombres = array_map(fn (RutaLaravel $r) => $r->getName(), $this->rutasPanel());

        foreach (['panel.usuarios.edit', 'panel.usuarios.update', 'panel.usuarios.destroy', 'panel.roles.datatable', 'panel.roles.reactivar'] as $esperada) {
            $this->assertContains($esperada, $nombres);
        }
    }

    public function test_invitado_es_redirigido_al_login(): void
    {
        foreach ($this->rutasPanel() as $ruta) {
            $this->pedir($ruta)->assertRedirect(route('login'));
        }
    }

    public function test_sin_permiso_403_y_con_permiso_accede(): void
    {
        $sinRol = $this->crearUsuario();

        foreach ($this->rutasPanel() as $ruta) {
            $permiso = $this->permiso($ruta);
            $etiqueta = $this->verbo($ruta).' '.$ruta->getName();

            if ($permiso === null) {
                $this->assertContains($ruta->getName(), ['panel.inicio'], "{$etiqueta} no exige permiso.");
                $this->actingAs($sinRol)->pedir($ruta)->assertOk();

                continue;
            }

            $this->assertNotNull(Permission::findByName($permiso), "El permiso {$permiso} de {$etiqueta} no existe.");

            $this->actingAs($sinRol)->pedir($ruta)->assertForbidden();

            $conPermiso = $this->crearUsuario();
            $conPermiso->givePermissionTo($permiso);
            $this->assertContains($this->actingAs($conPermiso)->pedir($ruta)->getStatusCode(), [200, 302], "{$etiqueta} con permiso");
        }
    }

    public function test_superadmin_accede_a_todo(): void
    {
        $admin = $this->superAdmin();

        foreach ($this->rutasPanel() as $ruta) {
            $this->assertContains(
                $this->actingAs($admin)->pedir($ruta)->getStatusCode(),
                [200, 302],
                $this->verbo($ruta).' '.$ruta->getName()
            );
        }
    }

    public function test_las_rutas_que_modifican_datos_no_aceptan_get(): void
    {
        foreach ($this->rutasPanel() as $ruta) {
            if (Str::endsWith($ruta->getName(), ['.store', '.update', '.destroy', '.reactivar', '.reenviar-link', '.datatable'])) {
                $this->assertNotContains('GET', $ruta->methods(), $ruta->getName());
            }
        }
    }

    public function test_paginas_de_error_en_espanol(): void
    {
        $this->get('/no-existe')->assertNotFound()->assertSee('Página no encontrada');

        $this->actingAs($this->crearUsuario(RolSistema::Preceptor))->get(route('panel.usuarios.index'))
            ->assertForbidden()->assertSee('Acceso denegado');
    }
}
