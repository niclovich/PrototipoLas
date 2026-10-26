<?php

use App\Enums\EstadoCaja;
use App\Enums\EstadoGeneral;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Finanzas y becas (Hoja 4, plan §3.8): cuotas → tipos_beca → convocatorias_beca → solicitudes_beca → becas
     * → cajas → detalles_cierre_caja → pagos → detalles_pago_cuota → facturas_comprobante.
     * Un solo medio de pago por Pago (decisión §7-5): pagos.medio_pago_id NOT NULL, sin tabla pago_medios.
     */
    public function up(): void
    {
        Schema::create('cuotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrera_id')->constrained('carreras')->restrictOnDelete();
            $table->foreignId('mes_id')->constrained('meses')->restrictOnDelete();
            $table->decimal('monto', 12, 2);
            $table->unsignedSmallInteger('anio');
            $table->string('estado_cuota', 20)->default(EstadoGeneral::Activo->value);
            $table->timestamps();

            // Carrera + año + Mes es único entre cuotas "Activo": MariaDB no tiene índices parciales, lo valida el Service del CU 33/34.
            $table->index(['carrera_id', 'anio', 'mes_id', 'estado_cuota']);
        });

        Schema::create('tipos_beca', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_beca', 100);
            $table->string('tipo_descuento', 20);
            $table->decimal('valor_descuento', 12, 2);
            $table->text('requisito_academico')->nullable();
            $table->text('requisito_economico')->nullable();
            $table->string('estado_beca', 20)->default(EstadoGeneral::Activo->value);
            $table->timestamps();
        });

        Schema::create('convocatorias_beca', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_beca_id')->constrained('tipos_beca')->restrictOnDelete();
            $table->unsignedSmallInteger('anio_lectivo');
            $table->date('fecha_desde');
            $table->date('fecha_hasta');
            $table->unsignedSmallInteger('cantidad_disponible');
            $table->string('estado_convocatoria', 20)->default(EstadoGeneral::Activo->value);
            $table->timestamps();
        });

        Schema::create('solicitudes_beca', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->restrictOnDelete();
            $table->foreignId('convocatoria_beca_id')->constrained('convocatorias_beca')->restrictOnDelete();
            $table->text('situacion_economica');
            $table->text('motivo_solicitud');
            $table->date('fecha_solicitud');
            $table->string('estado_solicitud', 20);
            $table->text('motivo_rechazo')->nullable();
            $table->timestamps();
        });

        Schema::create('becas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->restrictOnDelete();
            $table->foreignId('convocatoria_beca_id')->constrained('convocatorias_beca')->restrictOnDelete();
            $table->foreignId('solicitud_beca_id')->unique()->constrained('solicitudes_beca')->restrictOnDelete();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->decimal('valor_beca', 12, 2);
            $table->string('estado_beca', 20)->default(EstadoGeneral::Activo->value);
            $table->text('motivo_baja')->nullable();
            $table->timestamps();

            $table->index(['alumno_id', 'estado_beca']);
        });

        Schema::create('cajas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->dateTime('fecha_apertura');
            $table->decimal('monto_inicial', 12, 2);
            $table->dateTime('fecha_cierre')->nullable();
            $table->decimal('monto_final', 12, 2)->nullable();
            $table->decimal('diferencia_total', 12, 2)->nullable();
            $table->text('observacion_cierre')->nullable();
            $table->string('estado_caja', 20)->default(EstadoCaja::Abierta->value);
            $table->timestamps();

            $table->index(['user_id', 'estado_caja']);
        });

        Schema::create('detalles_cierre_caja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caja_id')->constrained('cajas')->restrictOnDelete();
            $table->foreignId('medio_pago_id')->constrained('medios_pago')->restrictOnDelete();
            $table->decimal('monto_esperado', 12, 2);
            $table->decimal('monto_contado', 12, 2);
            $table->decimal('diferencia', 12, 2);
            $table->timestamps();

            $table->unique(['caja_id', 'medio_pago_id']);
        });

        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->restrictOnDelete();
            $table->foreignId('caja_id')->constrained('cajas')->restrictOnDelete();
            $table->foreignId('medio_pago_id')->constrained('medios_pago')->restrictOnDelete();
            $table->dateTime('fecha_pago');
            $table->decimal('monto_total', 12, 2);
            $table->string('estado_pago', 20)->default(EstadoGeneral::Activo->value);
            $table->timestamps();

            $table->index(['alumno_id', 'estado_pago']);
            $table->index(['caja_id', 'estado_pago']);
            $table->index(['caja_id', 'medio_pago_id']);
        });

        Schema::create('detalles_pago_cuota', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pago_id')->constrained('pagos')->restrictOnDelete();
            $table->foreignId('cuota_id')->constrained('cuotas')->restrictOnDelete();
            $table->foreignId('beca_id')->nullable()->constrained('becas')->restrictOnDelete();
            $table->decimal('monto_aplicado', 12, 2);
            $table->decimal('monto_bonificado', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('facturas_comprobante', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pago_id')->unique()->constrained('pagos')->restrictOnDelete();
            $table->unsignedInteger('numero_comprobante')->unique();
            $table->date('fecha_emision');
            $table->string('estado_factura', 20)->default(EstadoGeneral::Activo->value);
            $table->uuid('uuid')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach ([
            'facturas_comprobante', 'detalles_pago_cuota', 'pagos', 'detalles_cierre_caja', 'cajas',
            'becas', 'solicitudes_beca', 'convocatorias_beca', 'tipos_beca', 'cuotas',
        ] as $tabla) {
            Schema::dropIfExists($tabla);
        }
    }
};
