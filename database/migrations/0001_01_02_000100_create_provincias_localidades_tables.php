<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catálogos Provincia y Localidad (Hoja 1). SoftDeletes para el ABM genérico de catálogos.
     */
    public function up(): void
    {
        Schema::create('provincias', function (Blueprint $table) {
            $table->id();
            $table->string('provincia', 100)->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('localidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provincia_id')->constrained('provincias')->restrictOnDelete();
            $table->string('localidad', 100);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['provincia_id', 'localidad']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('localidades');
        Schema::dropIfExists('provincias');
    }
};
