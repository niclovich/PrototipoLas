<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Roles/permisos, admin y catálogos siempre; datos de demostración solo en local.
     */
    public function run(): void
    {
        $this->call([
            RolesPermisosSeeder::class,
            AdminSeeder::class,
            CatalogosSeeder::class,
        ]);

        if (app()->environment('local')) {
            $this->call(DemoSeeder::class);
        }
    }
}
