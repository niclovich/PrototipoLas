<?php

use App\Enums\EstadoCuenta;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * users = clase Usuario de la tesis (Hoja 1). localidad_id se agrega en E1,
     * cuando exista la tabla localidades (plan general §3.2 y §3.10).
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('dni_usuario', 15)->unique();
            $table->string('apellido_usuario', 100);
            $table->string('nombre_usuario', 100);
            $table->date('fecha_nacimiento')->nullable();
            $table->string('sexo', 20)->nullable();
            $table->string('estado_civil', 30)->nullable();
            $table->string('telefono_usuario', 30)->nullable();
            $table->string('celular_usuario', 30)->nullable();
            $table->string('email', 150)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('direccion_usuario', 200)->nullable();
            $table->string('barrio_usuario', 100)->nullable();
            $table->string('pais_usuario', 80)->nullable();
            $table->string('foto_usuario')->nullable();
            $table->string('password')->nullable();
            $table->date('fecha_alta');
            $table->string('estado_cuenta', 20)->default(EstadoCuenta::Activo->value)->index();
            $table->rememberToken();
            $table->timestamps();

            $table->index(['apellido_usuario', 'nombre_usuario']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
