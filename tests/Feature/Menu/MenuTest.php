<?php

namespace Tests\Feature\Menu;

use App\Enums\RolSistema;
use JeroenNoten\LaravelAdminLte\AdminLte;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

/**
 * Cada rol ve en el menú lateral solo los ítems de sus permisos (config/adminlte.php 'can').
 */
class MenuTest extends TestCase
{
    use ConRolesYPermisos;

    /** @return array<string, string> texto del ítem => permiso */
    private function itemsDelMenu(): array
    {
        return collect(config('adminlte.menu'))
            ->filter(fn ($item) => isset($item['text'], $item['can']))
            ->mapWithKeys(fn ($item) => [$item['text'] => $item['can']])
            ->all();
    }

    /** AdminLTE arma el menú una vez por instancia de la app; en producción cada request es una instancia nueva. */
    private function inicioComo(RolSistema $rol)
    {
        $this->app->forgetInstance(AdminLte::class);

        return $this->actingAs($this->crearUsuario($rol))->get(route('panel.inicio'))->assertOk();
    }

    public function test_cada_rol_ve_solo_sus_items(): void
    {
        foreach (RolSistema::cases() as $rol) {
            if ($rol === RolSistema::Alumno) {
                continue; // no puede ingresar sin legajo Activo (E0); su menú se prueba en CU5.
            }

            $html = $this->inicioComo($rol)->getContent();

            foreach ($this->itemsDelMenu() as $texto => $permiso) {
                $debeVer = $rol === RolSistema::SuperAdmin || in_array($permiso, config("permisos.roles.{$rol->value}"), true);
                $ruta = collect(config('adminlte.menu'))->firstWhere('text', $texto)['route'];

                $this->assertSame(
                    $debeVer,
                    str_contains($html, 'href="'.route($ruta).'"'),
                    "{$rol->value} ".($debeVer ? 'debería' : 'no debería').' ver el ítem '.$texto
                );
            }
        }
    }

    public function test_ejemplos_concretos(): void
    {
        $this->inicioComo(RolSistema::Cajero)
            ->assertSee('Cajas')->assertSee('FINANZAS')->assertDontSee('ACADÉMICO')->assertDontSee('Usuarios');

        $this->inicioComo(RolSistema::Coordinador)
            ->assertSee('Carreras')->assertSee('Actas de Examen')->assertSee('Cuotas por Carrera')->assertDontSee('Cajas');
    }
}
