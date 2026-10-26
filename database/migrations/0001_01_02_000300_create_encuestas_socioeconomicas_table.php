<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Usuario 1..1 — 0..1 EncuestaSocioeconomica (CU1 P3). */
    public function up(): void
    {
        Schema::create('encuestas_socioeconomicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->restrictOnDelete();
            foreach ([
                'tiene_hijos', 'vive_solo', 'padres_viven', 'madre_trabaja', 'padre_trabaja', 'trabaja',
                'recibe_ayuda_economica', 'estudios_superiores_previos', 'posee_discapacidad',
                'miembro_poblacion_indigena', 'hablante_lenguas_indigenas', 'sabe_usar_pc', 'es_usuario_internet',
            ] as $columna) {
                $table->boolean($columna)->default(false);
            }
            $table->string('nivel_estudios_madre', 50)->nullable();
            $table->string('nivel_estudios_padre', 50)->nullable();
            $table->string('idiomas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('encuestas_socioeconomicas');
    }
};
