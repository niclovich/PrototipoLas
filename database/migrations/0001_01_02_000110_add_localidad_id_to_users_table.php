<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Usuario 1..* "nació en/reside en" 1..1 Localidad. Nullable en BD porque los usuarios técnicos
     * (SUPERADMIN, alta desde Administración) no cargan domicilio; Personal (CU 25) y Usuarios Alumno
     * (CU 9 / CU1) la exigen en su FormRequest (ver docs/diferencias-tesis.md).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('localidad_id')->nullable()->after('pais_usuario')->constrained('localidades')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('localidad_id');
        });
    }
};
