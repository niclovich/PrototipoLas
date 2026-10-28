<?php

namespace App\Services\Academico;

use App\Enums\EstadoCarrera;
use App\Models\Carrera;
use App\Models\Duracion;
use App\Models\Modalidad;
use App\Rules\ResolucionMinisterial;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * CU 3 Registrar y CU 4 Modificar datos de una Carrera (Coordinador de Carrera).
 */
class CarreraService
{
    /** CU 3 alt. 4.1 y CU 4 alt. 8.1. */
    public const MENSAJE_CARRERA_EXISTENTE = 'Carrera ya existente';

    /** Títulos de la Confirmación (wireframe Registrar_P3; D6 de E4 para Modificar). */
    public const CONFIRMACION_REGISTRO = 'Carrera registrada exitosamente';

    public const CONFIRMACION_MODIFICACION = 'Carrera modificada exitosamente';

    /** Tooltip de los campos deshabilitados cuando hay Plan de Estudio activo (D7 de E4). */
    public const AVISO_PLAN_ACTIVO = 'La carrera posee un Plan de Estudio activo';

    /** CU 3 pasos 5–7: modalidades vigentes (y la actual de la carrera aunque se haya dado de baja). */
    public function modalidades(?Carrera $carrera = null): Collection
    {
        return Modalidad::query()
            ->withTrashed()
            ->where(fn ($q) => $q->whereNull('deleted_at')->when($carrera?->modalidad_id, fn ($w, $id) => $w->orWhere('id', $id)))
            ->orderBy('modalidad')
            ->get(['id', 'modalidad']);
    }

    /** CU 3 pasos 8–10: duraciones vigentes (y la actual de la carrera aunque se haya dado de baja). */
    public function duraciones(?Carrera $carrera = null): Collection
    {
        return Duracion::query()
            ->withTrashed()
            ->where(fn ($q) => $q->whereNull('deleted_at')->when($carrera?->duracion_id, fn ($w, $id) => $w->orWhere('id', $id)))
            ->orderBy('cantidad_anios')
            ->get(['id', 'duracion']);
    }

    /** CU 4 paso 4: ¿la carrera tiene un Plan de Estudio "Activo"? */
    public function poseePlanActivo(Carrera $carrera): bool
    {
        return $carrera->planesEstudio()->activos()->exists();
    }

    /**
     * CU 3 paso 16: registra la carrera en estado "Pendiente" (lo fija el servidor).
     *
     * @param  array{nombre_carrera: string, resolucion_carrera: string, modalidad_id: int, duracion_id: int}  $datos
     */
    public function registrar(array $datos): Carrera
    {
        return DB::transaction(function () use ($datos) {
            $carrera = new Carrera([
                'nombre_carrera' => $datos['nombre_carrera'],
                'resolucion_carrera' => ResolucionMinisterial::normalizar($datos['resolucion_carrera']),
                'estado_carrera' => EstadoCarrera::Pendiente,
            ]);
            $carrera->modalidad()->associate($datos['modalidad_id']);
            $carrera->duracion()->associate($datos['duracion_id']);
            try {
                $carrera->save();
            } catch (UniqueConstraintViolationException) {
                throw $this->nombreDuplicado();
            }

            return $carrera;
        });
    }

    /**
     * CU 4 paso 10: con Plan de Estudio activo solo cambia el Estado; si no, todos los datos.
     *
     * @param  array<string, mixed>  $datos
     */
    public function modificar(Carrera $carrera, array $datos): Carrera
    {
        return DB::transaction(function () use ($carrera, $datos) {
            $carrera = Carrera::query()->lockForUpdate()->findOrFail($carrera->getKey());
            $carrera->estado_carrera = $datos['estado_carrera'];

            if (! $this->poseePlanActivo($carrera)) {
                $carrera->nombre_carrera = $datos['nombre_carrera'];
                $carrera->resolucion_carrera = ResolucionMinisterial::normalizar($datos['resolucion_carrera']);
                $carrera->modalidad()->associate($datos['modalidad_id']);
                $carrera->duracion()->associate($datos['duracion_id']);
            }

            try {
                $carrera->save();
            } catch (UniqueConstraintViolationException) {
                throw $this->nombreDuplicado();
            }

            return $carrera;
        });
    }

    /**
     * Líneas de la Confirmación (wireframe Registrar_P3).
     *
     * @return list<string>
     */
    public function lineasConfirmacion(Carrera $carrera): array
    {
        $carrera->loadMissing(['modalidad', 'duracion']);

        return [
            "Carrera: {$carrera->nombre_carrera}",
            "Modalidad: {$carrera->modalidad->modalidad} · Duración: {$carrera->duracion->duracion}",
            "Estado: {$carrera->estado_carrera->label()}",
        ];
    }

    /** Traduce la carrera por el nombre entre la validación HTTP y la escritura. */
    private function nombreDuplicado(): ValidationException
    {
        return ValidationException::withMessages(['nombre_carrera' => self::MENSAJE_CARRERA_EXISTENTE]);
    }
}
