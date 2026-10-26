<?php

namespace Database\Factories;

use App\Enums\EstadoCuenta;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password;

    public function definition(): array
    {
        return [
            'dni_usuario' => (string) fake()->unique()->numberBetween(10000000, 49999999),
            'apellido_usuario' => fake()->lastName(),
            'nombre_usuario' => fake()->firstName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= 'Clave1234',
            'fecha_alta' => today(),
            'estado_cuenta' => EstadoCuenta::Activo,
            'remember_token' => Str::random(10),
        ];
    }

    public function sinVerificar(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }

    public function inactivo(): static
    {
        return $this->state(fn () => ['estado_cuenta' => EstadoCuenta::Inactivo]);
    }

    /** Alta por administrador: todavía no definió la contraseña. */
    public function sinContrasenia(): static
    {
        return $this->state(fn () => ['password' => null, 'email_verified_at' => null]);
    }
}
