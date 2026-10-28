<?php

namespace App\Services\Alumnos;

use App\Enums\EstadoAlumno;
use App\Enums\EstadoGeneral;
use App\Enums\EstadoLegajo;
use App\Exceptions\OperacionNoPermitida;
use App\Mail\InscripcionRegistrada;
use App\Models\Alumno;
use App\Models\Cuota;
use App\Models\DocumentoPresentado;
use App\Models\TipoDocumento;
use App\Services\Finanzas\DeudaCuotasService;
use App\Services\Numeracion\NumeradorService;
use App\Services\Usuarios\UsuarioService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * CU2 "Registrar datos de Inscripción de Alumno" (colaboración Fig. 45): documentación, Cuota de Inscripción pagada,
 * legajo único, estado "Inscripto" con legajo "Activo", correo al alumno y link para definir la contraseña (§7-18).
 */
class InscripcionService
{
    /** Alt. 19.1 (texto actualizado de la spec). */
    public const MENSAJE_INSCRIPCION_NO_PAGADA = 'El alumno no registra el pago de la Cuota de Inscripción';

    /** Alt. 7.1 y 11.1. */
    public const MENSAJE_FORMATO = 'Formato Incompatible';

    /** E14-D4 (propio). */
    public const MENSAJE_YA_INSCRIPTO = 'El alumno ya se encuentra inscripto.';

    public const CONFIRMACION = 'Inscripcion registrada exitosamente';

    /** Campo del formulario => TipoDocumento (orden de P2: Certificado de Título y Fotocopia de DNI). */
    public const DOCUMENTOS = [
        'documento_titulo' => TipoDocumento::CERTIFICADO_TITULO,
        'documento_dni' => TipoDocumento::FOTOCOPIA_DNI,
    ];

    public function __construct(
        private readonly DeudaCuotasService $deuda,
        private readonly NumeradorService $numerador,
        private readonly UsuarioService $usuarios,
    ) {}

    /** @throws OperacionNoPermitida si el alumno ya no está Preinscripto. */
    public function verificarPreinscripto(Alumno $alumno): void
    {
        if ($alumno->estado_alumno !== EstadoAlumno::Preinscripto) {
            throw new OperacionNoPermitida(self::MENSAJE_YA_INSCRIPTO);
        }
    }

    /** Pasos 16–18: Cuota de Inscripción del año lectivo y su estado de pago. */
    public function cuotaInscripcion(Alumno $alumno): ?Cuota
    {
        return $this->deuda->cuotaInscripcionVigente($alumno);
    }

    public function cuotaPagada(Alumno $alumno, ?Cuota $cuota): bool
    {
        return $cuota !== null && $this->deuda->inscripcionPagada($alumno, $cuota->anio);
    }

    /**
     * Documentos "Activo" ya presentados por el usuario (en CU1), por campo del formulario (E14-D3).
     *
     * @return array<string, DocumentoPresentado>
     */
    public function documentosPresentados(Alumno $alumno): array
    {
        $presentados = $alumno->usuario->documentos()->with('tipoDocumento')
            ->where('estado_documento', EstadoGeneral::Activo->value)->latest('id')->get();

        return collect(self::DOCUMENTOS)
            ->map(fn (string $tipo) => $presentados->first(fn (DocumentoPresentado $d) => $d->tipoDocumento?->descripcion === $tipo))
            ->filter()
            ->all();
    }

    /**
     * Pasos 19–26.
     *
     * @param  array<string, UploadedFile>  $archivos  campo de self::DOCUMENTOS => PDF adjuntado
     *
     * @throws OperacionNoPermitida
     */
    public function registrar(Alumno $alumno, array $archivos): Alumno
    {
        $guardados = [];

        try {
            $alumno = DB::transaction(function () use ($alumno, $archivos, &$guardados) {
                $alumno = Alumno::query()->with(['usuario', 'carrera'])->lockForUpdate()->findOrFail($alumno->id);
                $this->verificarPreinscripto($alumno);

                $cuota = $this->cuotaInscripcion($alumno);
                if (! $this->cuotaPagada($alumno, $cuota)) {
                    throw new OperacionNoPermitida(self::MENSAJE_INSCRIPCION_NO_PAGADA);
                }

                $alumno->update([
                    'nro_legajo' => $this->numerador->siguiente('alumnos', 'nro_legajo', config('las.legajo_inicial')),
                    'estado_alumno' => EstadoAlumno::Inscripto,
                    'estado_legajo' => EstadoLegajo::Activo,
                ]);

                foreach ($archivos as $campo => $archivo) {
                    $guardados[] = $this->guardarDocumento($alumno, self::DOCUMENTOS[$campo], $archivo);
                }

                Mail::to($alumno->usuario)->queue(new InscripcionRegistrada($alumno));
                if ($alumno->usuario->password === null) {
                    $this->usuarios->enviarLinkAlta($alumno->usuario);
                }

                return $alumno;
            });
        } catch (Throwable $error) {
            Storage::disk(PreinscripcionService::DISCO_DOCUMENTOS)->delete($guardados);

            throw $error;
        }

        return $alumno;
    }

    /** Colaboración 7*: crea el DocumentoPresentado (el anterior del mismo tipo queda "Inactivo"). */
    private function guardarDocumento(Alumno $alumno, string $tipo, UploadedFile $archivo): string
    {
        $tipoDocumento = TipoDocumento::query()->where('descripcion', $tipo)->firstOrFail();
        $ruta = $archivo->store("documentos/{$alumno->user_id}", PreinscripcionService::DISCO_DOCUMENTOS);

        $alumno->usuario->documentos()->where('tipo_documento_id', $tipoDocumento->id)
            ->where('estado_documento', EstadoGeneral::Activo->value)
            ->update(['estado_documento' => EstadoGeneral::Inactivo->value]);

        $alumno->usuario->documentos()->create([
            'tipo_documento_id' => $tipoDocumento->id,
            'nombre_documento' => $tipo,
            'fecha_presentacion' => today(),
            'archivo_documento' => $ruta,
            'estado_documento' => EstadoGeneral::Activo,
        ]);

        return $ruta;
    }
}
