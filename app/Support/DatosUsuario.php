<?php

namespace App\Support;

/**
 * Datos de Usuario (Hoja 1) en la capa de dominio: campos que guarda cada alta y normalización
 * de DNI y correo. La usan los FormRequests (ReglasDatosUsuario) y los servicios (UsuarioService,
 * UsuarioAlumnoService), así el servicio no depende de la capa HTTP.
 */
final class DatosUsuario
{
    /** Campos de User que guarda el CU 9 (sin Provincia, que solo filtra la Localidad, ni el archivo de foto). */
    public const CAMPOS_ALUMNO = [
        'dni_usuario', 'apellido_usuario', 'nombre_usuario', 'fecha_nacimiento', 'sexo', 'estado_civil',
        'email', 'telefono_usuario', 'celular_usuario', 'direccion_usuario', 'barrio_usuario', 'localidad_id',
    ];

    public static function normalizarDni(?string $dni): string
    {
        return preg_replace('/\D/', '', (string) $dni);
    }

    public static function normalizarEmail(?string $email): string
    {
        return mb_strtolower(trim((string) $email));
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed> solo las claves presentes (dni_usuario, email), normalizadas
     */
    public static function normalizar(array $datos): array
    {
        return array_filter([
            'email' => array_key_exists('email', $datos) ? self::normalizarEmail($datos['email']) : null,
            'dni_usuario' => array_key_exists('dni_usuario', $datos) ? self::normalizarDni($datos['dni_usuario']) : null,
        ], fn ($valor) => $valor !== null);
    }

    /**
     * @param  array<string, mixed>  $datos
     * @param  list<string>  $campos
     * @return array<string, mixed>
     */
    public static function soloCampos(array $datos, array $campos): array
    {
        return [...array_intersect_key($datos, array_flip($campos)), ...self::normalizar(array_intersect_key($datos, array_flip($campos)))];
    }
}
