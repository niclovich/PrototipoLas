<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Crea los roles, permisos, usuarios, catálogos y datos de demostración.
     */
    public function run(): void
    {
        $this->call([
            RolesPermisosSeeder::class,
            AdminSeeder::class,
            CatalogosSeeder::class,
        ]);

        $this->call(DemoSeeder::class);
    }
}
