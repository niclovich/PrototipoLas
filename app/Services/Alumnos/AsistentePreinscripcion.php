<?php

namespace App\Services\Alumnos;

use App\Models\User;
use Illuminate\Contracts\Session\Session;

/**
 * Estado del asistente de CU1 en la sesión (decisión E3-D7, opción B): nada se persiste hasta
 * "Confirmar preinscripcion" (P5). Guarda el DNI de P1, el usuario existente (si lo hay), los datos de P2
 * (con la ruta de la foto temporal), la Encuesta de P3 y la carrera y los documentos temporales de P4.
 */
class AsistentePreinscripcion
{
    private const CLAVE = 'asistente_preinscripcion';

    public function __construct(
        private readonly Session $sesion,
        private readonly PreinscripcionService $preinscripciones,
    ) {}

    /** P1 "Buscar": empieza un registro nuevo y descarta los archivos temporales del anterior. */
    public function iniciar(string $dni, ?User $existente): void
    {
        $this->descartar();
        $this->sesion->put(self::CLAVE, ['dni' => $dni, 'usuario_id' => $existente?->id]);
    }

    /** Borra los archivos temporales y el estado (cancelación o nueva búsqueda). */
    public function descartar(): void
    {
        $this->preinscripciones->descartarTemporales($this->fotoTemporal(), $this->documentos());
        $this->terminar();
    }

    /** Después de registrar: los temporales ya se movieron a su carpeta definitiva. */
    public function terminar(): void
    {
        $this->sesion->forget(self::CLAVE);
    }

    public function iniciado(): bool
    {
        return $this->obtener('dni') !== null;
    }

    public function dni(): ?string
    {
        return $this->obtener('dni');
    }

    public function usuarioExistente(): ?User
    {
        $id = $this->obtener('usuario_id');

        return $id ? User::query()->find($id) : null;
    }

    public function esNuevo(): bool
    {
        return $this->iniciado() && $this->obtener('usuario_id') === null;
    }

    /** @param  array<string, mixed>  $datos  DatosPersonalesAlumnoRequest::datosSesion() */
    public function guardarDatosPersonales(array $datos, ?string $fotoTemporal): void
    {
        $this->poner('datos_personales', $datos);
        $this->poner('foto', $fotoTemporal);
    }

    /** @return array<string, mixed>|null */
    public function datosPersonales(): ?array
    {
        return $this->obtener('datos_personales');
    }

    public function fotoTemporal(): ?string
    {
        return $this->obtener('foto');
    }

    /** @param  array<string, mixed>  $encuesta */
    public function guardarEncuesta(array $encuesta): void
    {
        $this->poner('encuesta', $encuesta);
    }

    /** @return array<string, mixed>|null */
    public function encuesta(): ?array
    {
        return $this->obtener('encuesta');
    }

    /**
     * @param  array{carrera_id: int, situacion_estudios: string, adeuda_materia: bool, observaciones?: ?string}  $carrera
     * @param  array<string, string>  $documentos  clave TipoDocumento => ruta temporal
     */
    public function guardarCarrera(array $carrera, array $documentos): void
    {
        $this->poner('carrera', $carrera);
        $this->poner('documentos', $documentos);
    }

    /** @return array{carrera_id: int, situacion_estudios: string, adeuda_materia: bool, observaciones?: ?string}|null */
    public function carrera(): ?array
    {
        return $this->obtener('carrera');
    }

    /** @return array<string, string> */
    public function documentos(): array
    {
        return $this->obtener('documentos') ?? [];
    }

    /** Pantallas a las que se puede llegar según lo cargado (acceso directo o recarga). */
    public function puedeVer(int $paso): bool
    {
        return match ($paso) {
            2 => $this->esNuevo(),
            3 => $this->esNuevo() && $this->datosPersonales() !== null,
            4 => $this->iniciado() && (! $this->esNuevo() || $this->encuesta() !== null),
            5 => $this->puedeVer(4) && $this->carrera() !== null,
            default => true,
        };
    }

    private function obtener(string $clave): mixed
    {
        return $this->sesion->get(self::CLAVE.'.'.$clave);
    }

    private function poner(string $clave, mixed $valor): void
    {
        $this->sesion->put(self::CLAVE.'.'.$clave, $valor);
    }
}
