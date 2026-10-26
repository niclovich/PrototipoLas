<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catálogos sin CU propio (plan §3.3): solo seeder + ABM genérico del SUPERADMIN (SoftDeletes).
     */
    public function up(): void
    {
        $this->catalogo('modalidades', 'modalidad', 50);

        Schema::create('duraciones', function (Blueprint $table) {
            $table->id();
            $table->string('duracion', 50)->unique();
            $table->unsignedTinyInteger('cantidad_anios');
            $table->timestamps();
            $table->softDeletes();
        });

        $this->catalogo('regimenes', 'regimen', 50);
        $this->catalogo('cuatrimestres', 'cuatrimestre', 50);
        $this->catalogo('llamados', 'llamado', 50);
        $this->catalogo('tipos_acta', 'descripcion', 50);
        $this->catalogo('roles_tribunal', 'descripcion', 50);
        $this->catalogo('tipos_documento', 'descripcion', 80);

        Schema::create('motivos', function (Blueprint $table) {
            $table->id();
            $table->string('descripcion', 150);
            $table->string('accion', 50)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['accion', 'descripcion']);
        });

        $this->catalogo('medios_pago', 'medio_pago', 50);
        $this->catalogo('tipos_gestion_titulo', 'descripcion', 80);
    }

    public function down(): void
    {
        foreach ([
            'tipos_gestion_titulo', 'medios_pago', 'motivos', 'tipos_documento', 'roles_tribunal',
            'tipos_acta', 'llamados', 'cuatrimestres', 'regimenes', 'duraciones', 'modalidades',
        ] as $tabla) {
            Schema::dropIfExists($tabla);
        }
    }

    private function catalogo(string $tabla, string $columna, int $largo): void
    {
        Schema::create($tabla, function (Blueprint $table) use ($columna, $largo) {
            $table->id();
            $table->string($columna, $largo)->unique();
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
