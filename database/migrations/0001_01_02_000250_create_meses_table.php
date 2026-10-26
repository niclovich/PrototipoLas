<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Clase Mes (Hoja 4): datos precargados sin CU propio. El id es el orden (1 Inscripción, 2 Marzo … 11 Diciembre).
     */
    public function up(): void
    {
        Schema::create('meses', function (Blueprint $table) {
            $table->id();
            $table->string('descripcion', 30)->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meses');
    }
};
