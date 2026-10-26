<?php

use App\Enums\EstadoCarrera;
use App\Enums\EstadoGeneral;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Académico (Hoja 2, plan §3.5): carreras → materias → planes_estudio → detalles_plan_estudio
     * → correlativas → comisiones → det_comisiones.
     */
    public function up(): void
    {
        Schema::create('carreras', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_carrera', 150)->unique();
            $table->string('resolucion_carrera', 50);
            $table->string('estado_carrera', 20)->default(EstadoCarrera::Pendiente->value)->index();
            $table->foreignId('modalidad_id')->constrained('modalidades')->restrictOnDelete();
            $table->foreignId('duracion_id')->constrained('duraciones')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('materias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_materia', 150)->unique();
            $table->string('estado_materia', 20)->default(EstadoGeneral::Activo->value)->index();
            $table->timestamps();
        });

        Schema::create('planes_estudio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrera_id')->constrained('carreras')->restrictOnDelete();
            $table->unsignedSmallInteger('anio_plan_estudio');
            $table->string('estado_plan_estudio', 20)->default(EstadoGeneral::Activo->value);
            $table->timestamps();

            $table->unique(['carrera_id', 'anio_plan_estudio']);
            $table->index(['carrera_id', 'estado_plan_estudio']);
        });

        Schema::create('detalles_plan_estudio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_estudio_id')->constrained('planes_estudio')->restrictOnDelete();
            $table->foreignId('materia_id')->constrained('materias')->restrictOnDelete();
            $table->foreignId('regimen_id')->constrained('regimenes')->restrictOnDelete();
            $table->foreignId('cuatrimestre_id')->constrained('cuatrimestres')->restrictOnDelete();
            $table->string('codigo_det_plan_estudio', 20);
            $table->unsignedSmallInteger('carga_horaria_det_plan_estudio');
            $table->unsignedTinyInteger('anio_curso_det_plan_estudio');
            $table->string('estado_det_plan_estudio', 20)->default(EstadoGeneral::Activo->value);
            $table->date('fecha_baja')->nullable();
            $table->timestamps();

            $table->unique(['plan_estudio_id', 'materia_id'], 'det_plan_materia_unique');
            $table->unique(['plan_estudio_id', 'codigo_det_plan_estudio'], 'det_plan_codigo_unique');
            $table->index(['plan_estudio_id', 'anio_curso_det_plan_estudio'], 'det_plan_anio_index');
        });

        Schema::create('correlativas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('detalle_plan_estudio_id')->constrained('detalles_plan_estudio')->restrictOnDelete();
            $table->foreignId('detalle_plan_correlativa_id')->constrained('detalles_plan_estudio')->restrictOnDelete();
            $table->string('p_cursar', 20)->nullable();
            $table->string('p_rendir', 20)->nullable();
            $table->timestamps();

            $table->unique(['detalle_plan_estudio_id', 'detalle_plan_correlativa_id'], 'correlativas_detalle_correlativa_unique');
        });

        Schema::create('comisiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrera_id')->constrained('carreras')->restrictOnDelete();
            $table->foreignId('modalidad_id')->constrained('modalidades')->restrictOnDelete();
            $table->string('codigo_comision', 20);
            $table->unsignedSmallInteger('capacidad_comision');
            $table->unsignedSmallInteger('carga_horaria_comision');
            $table->string('horario_comision', 100);
            $table->unsignedTinyInteger('anio_curso');
            $table->unsignedSmallInteger('anio_lectivo');
            $table->string('estado_comision', 20)->default(EstadoGeneral::Activo->value);
            $table->timestamps();

            $table->unique(['carrera_id', 'codigo_comision', 'anio_curso', 'anio_lectivo'], 'comisiones_carrera_codigo_anio_unique');
            $table->index(['carrera_id', 'anio_lectivo', 'estado_comision']);
        });

        Schema::create('det_comisiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comision_id')->constrained('comisiones')->restrictOnDelete();
            $table->foreignId('detalle_plan_estudio_id')->constrained('detalles_plan_estudio')->restrictOnDelete();
            $table->string('estado_det_comision', 20)->default(EstadoGeneral::Activo->value);
            $table->timestamps();

            $table->unique(['comision_id', 'detalle_plan_estudio_id']);
        });
    }

    public function down(): void
    {
        foreach (['det_comisiones', 'comisiones', 'correlativas', 'detalles_plan_estudio', 'planes_estudio', 'materias', 'carreras'] as $tabla) {
            Schema::dropIfExists($tabla);
        }
    }
};
