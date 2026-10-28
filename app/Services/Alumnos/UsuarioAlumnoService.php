<?php

namespace App\Services\Alumnos;

use App\Enums\RolSistema;
use App\Models\User;
use App\Services\Usuarios\UsuarioService;
use App\Support\DatosUsuario;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * CU 9 "Registrar Datos de Usuarios Alumno" (lo invoca CU1, alt. 4.3). Recibe datos ya validados
 * (los mismos que el wizard guarda en sesión y re-valida en P5) y persiste Usuario + Encuesta en una sola
 * transacción, que se anida si CU1 la llama desde la suya. No envía correos: el link para definir la contraseña
 * lo manda CU2 (decisión §7-18).
 *
 * Foto (opción B de D7): en P2 se sube con guardarFotoTemporal() y la ruta viaja en la sesión; en P5
 * registrar() la mueve a la carpeta definitiva. Si el alta falla, la foto se borra.
 */
class UsuarioAlumnoService
{
    /** Paso 27 (decisión D8 de E3). */
    public const MENSAJE_REGISTRO_EXITOSO = 'El Usuario Alumno se registró correctamente.';

    public const DIRECTORIO_FOTOS = 'fotos-usuarios';

    public const DIRECTORIO_TEMPORAL = 'fotos-usuarios/temporales';

    public function __construct(private readonly UsuarioService $usuarios) {}

    /** P2: guarda la foto subida en la carpeta temporal y devuelve su ruta (para la sesión del wizard). */
    public function guardarFotoTemporal(UploadedFile $foto): string
    {
        return $foto->store(self::DIRECTORIO_TEMPORAL, 'public');
    }

    /** Si el actor abandona el wizard o cambia la foto. */
    public function descartarFotoTemporal(?string $ruta): void
    {
        if ($ruta && Str::startsWith($ruta, self::DIRECTORIO_TEMPORAL.'/')) {
            Storage::disk('public')->delete($ruta);
        }
    }

    /**
     * Pasos 9–10: Usuario en estado "Activo" (rol Alumno, sin contraseña, fecha de alta hoy) y su Encuesta.
     *
     * @param  array<string, mixed>  $datosPersonales  campos de DatosUsuario::CAMPOS_ALUMNO (fecha en Y-m-d)
     * @param  array<string, mixed>  $encuesta  campos de EncuestaSocioeconomicaRequest
     * @param  string|null  $rutaFotoTemporal  devuelta por guardarFotoTemporal()
     *
     * @throws ValidationException si el DNI o el correo ya están registrados
     */
    public function registrar(array $datosPersonales, array $encuesta, ?string $rutaFotoTemporal = null): User
    {
        $datos = [
            ...DatosUsuario::soloCampos($datosPersonales, DatosUsuario::CAMPOS_ALUMNO),
            'pais_usuario' => config('las.pais_por_defecto'),
        ];
        $rutaFoto = $this->confirmarFoto($rutaFotoTemporal);

        try {
            return DB::transaction(function () use ($datos, $encuesta, $rutaFoto) {
                $this->verificarDisponibles($datos['dni_usuario'], $datos['email']);

                try {
                    $usuario = $this->usuarios->crearCuenta([...$datos, 'foto_usuario' => $rutaFoto], RolSistema::Alumno->value, null);
                } catch (UniqueConstraintViolationException $error) {
                    // Otro registro tomó el DNI o el correo entre la verificación y el alta.
                    throw $this->errorDeDuplicado($error);
                }

                $usuario->encuesta()->create($encuesta);

                return $usuario;
            });
        } catch (Throwable $error) {
            if ($rutaFoto) {
                Storage::disk('public')->delete($rutaFoto);
            }

            throw $error;
        }
    }

    /** Mueve la foto temporal a la carpeta definitiva. */
    private function confirmarFoto(?string $rutaTemporal): ?string
    {
        if (! $rutaTemporal || ! Storage::disk('public')->exists($rutaTemporal)) {
            return null;
        }

        $definitiva = self::DIRECTORIO_FOTOS.'/'.basename($rutaTemporal);
        Storage::disk('public')->move($rutaTemporal, $definitiva);

        return $definitiva;
    }

    /** Alt. A1/A2 del plan E3: DNI y correo libres (con bloqueo, por carreras entre P1 y la confirmación). */
    private function verificarDisponibles(string $dni, string $email): void
    {
        if (User::query()->where('dni_usuario', $dni)->lockForUpdate()->exists()) {
            throw ValidationException::withMessages(['dni_usuario' => UsuarioService::MENSAJE_DNI_REGISTRADO]);
        }

        if (User::query()->where('email', $email)->lockForUpdate()->exists()) {
            throw ValidationException::withMessages(['email' => UsuarioService::MENSAJE_CORREO_REGISTRADO]);
        }
    }

    /** Distingue el índice violado (users_email_unique vs users_dni_usuario_unique). */
    private function errorDeDuplicado(UniqueConstraintViolationException $error): ValidationException
    {
        // MariaDB/MySQL: "Duplicate entry '…' for key 'users_email_unique'" (el SQL del mensaje también nombra email).
        preg_match("/for key '([^']+)'/", $error->getMessage(), $indice);

        return str_contains($indice[1] ?? '', 'email')
            ? ValidationException::withMessages(['email' => UsuarioService::MENSAJE_CORREO_REGISTRADO])
            : ValidationException::withMessages(['dni_usuario' => UsuarioService::MENSAJE_DNI_REGISTRADO]);
    }
}
