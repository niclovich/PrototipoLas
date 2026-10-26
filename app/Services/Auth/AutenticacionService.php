<?php

namespace App\Services\Auth;

use App\Contracts\VerificadorLegajoAlumno;
use App\Enums\EstadoCuenta;
use App\Enums\RolSistema;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Lógica de Fortify::authenticateUsing (plan §3.2 y §5.4).
 */
class AutenticacionService
{
    public const MENSAJE_NO_ACTIVO = 'Este usuario no está activo';

    public function __construct(private readonly VerificadorLegajoAlumno $verificadorLegajo) {}

    /**
     * @throws ValidationException si es un Alumno sin legajo Activo
     */
    public function autenticar(string $email, string $password): ?User
    {
        $usuario = User::query()
            ->where('email', mb_strtolower(trim($email)))
            ->conEstadoCuenta(EstadoCuenta::Activo)
            ->first();

        // Se valida la contraseña antes que el legajo para no revelar qué cuentas existen.
        if (! $usuario || ! $usuario->password || ! Hash::check($password, $usuario->password)) {
            return null;
        }

        if ($usuario->tieneRol(RolSistema::Alumno) && ! $this->verificadorLegajo->tieneLegajoActivo($usuario)) {
            throw ValidationException::withMessages(['email' => self::MENSAJE_NO_ACTIVO]);
        }

        return $usuario;
    }
}
