<?php

use App\Enums\Asentada;
use App\Enums\EstadoActa;
use App\Enums\EstadoDetalleActa;
use App\Enums\EstadoGeneral;
use App\Enums\EstadoTramite;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exámenes y título (Hoja 3, plan §3.7): tribunales → actas_examen → integrantes_tribunal → detalles_acta → titulos_tramite.
     */
    public function up(): void
    {
        Schema::create('tribunales', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::create('actas_examen', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('nro_acta')->unique();
            $table->foreignId('detalle_plan_estudio_id')->constrained('detalles_plan_estudio')->restrictOnDelete();
            $table->foreignId('modalidad_id')->constrained('modalidades')->restrictOnDelete();
            $table->foreignId('llamado_id')->constrained('llamados')->restrictOnDelete();
            $table->foreignId('tipo_acta_id')->constrained('tipos_acta')->restrictOnDelete();
            $table->foreignId('tribunal_id')->unique()->constrained('tribunales')->restrictOnDelete();
            $table->date('fecha_acta');
            $table->string('estado_acta', 20)->default(EstadoActa::Pendiente->value);
            $table->char('asentada', 1)->default(Asentada::No->value);
            $table->unsignedInteger('nro_folio')->nullable();
            $table->timestamps();

            $table->index(['detalle_plan_estudio_id', 'estado_acta']);
        });

        Schema::create('integrantes_tribunal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tribunal_id')->constrained('tribunales')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('rol_tribunal_id')->constrained('roles_tribunal')->restrictOnDelete();
            $table->string('estado_integrante', 20)->default(EstadoGeneral::Activo->value);
            $table->timestamps();

            $table->index(['tribunal_id', 'estado_integrante']);
        });

        Schema::create('detalles_acta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('acta_examen_id')->constrained('actas_examen')->restrictOnDelete();
            $table->foreignId('alumno_id')->constrained('alumnos')->restrictOnDelete();
            $table->decimal('nota_acta', 4, 2)->nullable();
            $table->string('observacion')->nullable();
            $table->string('estado_detalle_acta', 20)->default(EstadoDetalleActa::Activo->value);
            $table->string('condicion_final', 20)->nullable();
            $table->uuid('uuid')->unique();
            $table->timestamps();

            $table->unique(['acta_examen_id', 'alumno_id']);
        });

        Schema::create('titulos_tramite', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->unique()->constrained('alumnos')->restrictOnDelete();
            $table->foreignId('tipo_gestion_titulo_id')->nullable()->constrained('tipos_gestion_titulo')->restrictOnDelete();
            $table->date('fecha_creacion');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_recepcion')->nullable();
            $table->date('fecha_retiro')->nullable();
            $table->string('estado_tramite', 20)->default(EstadoTramite::SinGestion->value);
            $table->text('observacion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['titulos_tramite', 'detalles_acta', 'integrantes_tribunal', 'actas_examen', 'tribunales'] as $tabla) {
            Schema::dropIfExists($tabla);
        }
    }
};
