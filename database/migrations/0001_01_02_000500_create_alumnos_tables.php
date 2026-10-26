<?php

use App\Enums\EstadoAlumno;
use App\Enums\EstadoAlumnoComision;
use App\Enums\EstadoGeneral;
use App\Enums\SituacionEstudios;
use App\Enums\TipoInscripcion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Alumnos (Hoja 2, plan §3.6): alumnos → documentos_presentados → novedades → alumno_comision → estados_curriculares.
     */
    public function up(): void
    {
        Schema::create('alumnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('carrera_id')->constrained('carreras')->restrictOnDelete();
            $table->foreignId('plan_estudio_id')->constrained('planes_estudio')->restrictOnDelete();
            $table->foreignId('comision_actual_id')->nullable()->constrained('comisiones')->restrictOnDelete();
            $table->unsignedInteger('nro_legajo')->nullable()->unique();
            $table->string('estado_alumno', 30)->default(EstadoAlumno::Preinscripto->value);
            $table->string('estado_legajo', 20)->nullable();
            $table->string('situacion_estudios', 20)->default(SituacionEstudios::EnCurso->value);
            $table->boolean('adeuda_materia')->default(false);
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'carrera_id']);
            $table->index(['estado_alumno', 'estado_legajo']);
        });

        Schema::create('documentos_presentados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('tipo_documento_id')->constrained('tipos_documento')->restrictOnDelete();
            $table->string('nombre_documento', 150);
            $table->date('fecha_presentacion');
            $table->string('archivo_documento');
            $table->string('estado_documento', 20)->default(EstadoGeneral::Activo->value);
            $table->timestamps();

            $table->index(['user_id', 'tipo_documento_id']);
        });

        Schema::create('novedades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('alumno_id')->nullable()->constrained('alumnos')->restrictOnDelete();
            $table->foreignId('motivo_id')->constrained('motivos')->restrictOnDelete();
            $table->date('fecha_novedad');
            $table->text('observacion_novedad')->nullable();
            $table->timestamps();
        });

        Schema::create('alumno_comision', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->restrictOnDelete();
            $table->foreignId('det_comision_id')->constrained('det_comisiones')->restrictOnDelete();
            $table->date('fecha_inscripcion');
            $table->string('estado_alumno_comision', 20)->default(EstadoAlumnoComision::Activo->value);
            $table->string('tipo_inscripcion', 20)->default(TipoInscripcion::PorComision->value);
            $table->string('condicion_final', 20)->nullable();
            foreach (['nota_p1', 'nota_p2', 'nota_r1', 'nota_r2'] as $nota) {
                $table->decimal($nota, 4, 2)->nullable();
            }
            $table->unsignedSmallInteger('faltas_alumno')->nullable();
            $table->timestamps();

            $table->index(['alumno_id', 'estado_alumno_comision']);
            $table->index(['det_comision_id', 'estado_alumno_comision']);
        });

        Schema::create('estados_curriculares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->restrictOnDelete();
            $table->foreignId('detalle_plan_estudio_id')->constrained('detalles_plan_estudio')->restrictOnDelete();
            $table->string('condicion', 20);
            $table->date('fecha_condicion');
            $table->decimal('nota_condicion', 4, 2)->nullable();
            $table->unsignedInteger('nro_acta')->nullable();
            $table->timestamps();

            $table->index(['alumno_id', 'detalle_plan_estudio_id', 'fecha_condicion'], 'estados_curriculares_alumno_detalle_fecha_index');
        });
    }

    public function down(): void
    {
        foreach (['estados_curriculares', 'alumno_comision', 'novedades', 'documentos_presentados', 'alumnos'] as $tabla) {
            Schema::dropIfExists($tabla);
        }
    }
};
