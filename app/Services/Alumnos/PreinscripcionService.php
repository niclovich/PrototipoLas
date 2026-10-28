<?php

namespace App\Services\Alumnos;

use App\Enums\EstadoAlumno;
use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Enums\SituacionEstudios;
use App\Models\Alumno;
use App\Models\Carrera;
use App\Models\TipoDocumento;
use App\Models\User;
use App\Services\Usuarios\UsuarioService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * CU1 "Registrar datos de Preinscripción de Alumno" (colaboración Fig. 43). Persiste todo en P5, en una sola
 * transacción: el Usuario y su Encuesta (CU 9, si el DNI no existía), el Alumno "Preinscripto" vinculado al
 * Usuario y a la Carrera (con su plan Activo) y los DocumentoPresentado vinculados al Usuario.
 */
class PreinscripcionService
{
    /** Alt. 4.1. */
    public const MENSAJE_DNI_INEXISTENTE = 'DNI Inexistente';

    /** Alt. 18.1. */
    public const MENSAJE_DOCUMENTACION_NO_ADJUNTADA = 'La documentación no fue adjuntada. Podrá presentarse durante el proceso de inscripción definitiva';

    /** Decisión E3-D11. */
    public const MENSAJE_DNI_PERSONAL = 'El DNI ingresado corresponde a un usuario del personal';

    /** Decisión E12-D2 (unique user + carrera; no está en la tesis). */
    public const MENSAJE_YA_EN_CARRERA = 'El alumno ya se encuentra registrado en la carrera seleccionada.';

    /** Paso 28 (decisión E12-D4). */
    public const MENSAJE_REGISTRO_EXITOSO = 'La preinscripción del alumno se registró correctamente.';

    /** Documentos de P4 (clave del formulario => TipoDocumento). */
    public const DOCUMENTOS = [
        'documento_dni' => TipoDocumento::FOTOCOPIA_DNI,
        'documento_titulo' => TipoDocumento::CERTIFICADO_TITULO,
    ];

    public const DISCO_DOCUMENTOS = 'local';

    public const DIRECTORIO_TEMPORAL = 'documentos/temporales';

    public function __construct(
        private readonly UsuarioService $usuarios,
        private readonly UsuarioAlumnoService $usuariosAlumno,
    ) {}

    /**
     * Paso 4: Usuario del DNI, o null si no existe (alt. 4.1). Rechaza el DNI del personal (E3-D11).
     *
     * @throws ValidationException
     */
    public function buscarUsuario(string $dni): ?User
    {
        $usuario = $this->usuarios->buscarPorDni($dni);

        if ($usuario && ! $usuario->tieneRol(RolSistema::Alumno)) {
            throw ValidationException::withMessages(['dni' => self::MENSAJE_DNI_PERSONAL]);
        }

        return $usuario;
    }

    /**
     * Paso 8: carreras "Activo" con plan de estudio Activo (el Alumno "cursa" 1..1 PlanEstudio).
     *
     * @return Collection<int, Carrera>
     */
    public function carrerasDisponibles(): Collection
    {
        return Carrera::query()->activas()->whereHas('planActivo')
            ->with(['modalidad', 'duracion'])->orderBy('nombre_carrera')->get();
    }

    /** Pasos 17–18: el PDF se guarda en una carpeta temporal privada hasta confirmar. */
    public function guardarDocumentoTemporal(UploadedFile $archivo): string
    {
        return $archivo->store(self::DIRECTORIO_TEMPORAL, self::DISCO_DOCUMENTOS);
    }

    /** @param  array<string, string>  $documentos */
    public function descartarTemporales(?string $foto, array $documentos): void
    {
        $this->usuariosAlumno->descartarFotoTemporal($foto);

        foreach ($documentos as $ruta) {
            if (Str::startsWith($ruta, self::DIRECTORIO_TEMPORAL.'/')) {
                Storage::disk(self::DISCO_DOCUMENTOS)->delete($ruta);
            }
        }
    }

    /**
     * Pasos 25–27 (y CU 9 pasos 9–10 si $existente es null).
     *
     * @param  array<string, mixed>|null  $datosPersonales  DatosPersonalesAlumnoRequest::paraUsuario()
     * @param  array<string, mixed>|null  $encuesta  EncuestaSocioeconomicaRequest::paraEncuesta()
     * @param  array{carrera_id: int, situacion_estudios: string, adeuda_materia: bool, observaciones?: ?string}  $preinscripcion
     * @param  array<string, string>  $documentos  clave de self::DOCUMENTOS => ruta temporal
     *
     * @throws ValidationException
     */
    public function registrar(?User $existente, ?array $datosPersonales, ?array $encuesta, ?string $fotoTemporal, array $preinscripcion, array $documentos): Alumno
    {
        $movidos = [];
        $usuarioNuevo = null;

        try {
            return DB::transaction(function () use ($existente, $datosPersonales, $encuesta, $fotoTemporal, $preinscripcion, $documentos, &$movidos, &$usuarioNuevo) {
                $carrera = Carrera::query()->activas()->with('planActivo')->lockForUpdate()->find($preinscripcion['carrera_id']);
                if (! $carrera?->planActivo) {
                    throw ValidationException::withMessages(['carrera_id' => 'Seleccione una carrera de la lista.']);
                }

                $usuario = $existente ?? $usuarioNuevo = $this->usuariosAlumno->registrar($datosPersonales ?? [], $encuesta ?? [], $fotoTemporal);

                if ($usuario->alumnos()->where('carrera_id', $carrera->id)->lockForUpdate()->exists()) {
                    throw ValidationException::withMessages(['carrera_id' => self::MENSAJE_YA_EN_CARRERA]);
                }

                $alumno = $usuario->alumnos()->create([
                    'carrera_id' => $carrera->id,
                    'plan_estudio_id' => $carrera->planActivo->id,
                    'estado_alumno' => EstadoAlumno::Preinscripto,
                    'situacion_estudios' => SituacionEstudios::from($preinscripcion['situacion_estudios']),
                    'adeuda_materia' => (bool) $preinscripcion['adeuda_materia'],
                    'observaciones' => $preinscripcion['observaciones'] ?? null,
                ]);

                foreach (self::DOCUMENTOS as $clave => $tipo) {
                    if (empty($documentos[$clave]) || ! Storage::disk(self::DISCO_DOCUMENTOS)->exists($documentos[$clave])) {
                        continue;
                    }
                    $destino = "documentos/{$usuario->id}/".basename($documentos[$clave]);
                    Storage::disk(self::DISCO_DOCUMENTOS)->move($documentos[$clave], $destino);
                    $movidos[$documentos[$clave]] = $destino;

                    $usuario->documentos()->create([
                        'tipo_documento_id' => TipoDocumento::query()->where('descripcion', $tipo)->valueOrFail('id'),
                        'nombre_documento' => $tipo,
                        'fecha_presentacion' => today(),
                        'archivo_documento' => $destino,
                        'estado_documento' => EstadoGeneral::Activo,
                    ]);
                }

                return $alumno;
            });
        } catch (Throwable $error) {
            // Los archivos movidos vuelven a la carpeta temporal (el asistente puede reintentar) y la foto del usuario revertido se borra.
            foreach ($movidos as $temporal => $destino) {
                Storage::disk(self::DISCO_DOCUMENTOS)->move($destino, $temporal);
            }
            if ($usuarioNuevo?->foto_usuario) {
                Storage::disk('public')->delete($usuarioNuevo->foto_usuario);
            }

            throw $error;
        }
    }
}
