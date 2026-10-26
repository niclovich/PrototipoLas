<?php

use App\Enums\EstadoGeneral;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Clase Rol de la tesis (idRol, nombreRol, descripcionRol, estadoRol) sobre la tabla roles de Spatie.
     */
    public function up(): void
    {
        Schema::table(config('permission.table_names.roles'), function (Blueprint $table) {
            $table->string('descripcion')->nullable()->after('guard_name');
            $table->string('estado', 20)->default(EstadoGeneral::Activo->value)->after('descripcion')->index();
        });
    }

    public function down(): void
    {
        Schema::table(config('permission.table_names.roles'), function (Blueprint $table) {
            $table->dropIndex(['estado']);
            $table->dropColumn(['descripcion', 'estado']);
        });
    }
};
