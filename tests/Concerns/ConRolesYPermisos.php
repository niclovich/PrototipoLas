<?php

namespace Tests\Concerns;

use Database\Seeders\RolesPermisosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Base de datos limpia (sistema_las_test) con roles y permisos sembrados.
 */
trait ConRolesYPermisos
{
    use RefreshDatabase;

    protected function setUpConRolesYPermisos(): void
    {
        $this->seed(RolesPermisosSeeder::class);
    }
}
