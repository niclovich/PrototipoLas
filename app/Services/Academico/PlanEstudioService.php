<?php

namespace App\Services\Academico;

use App\Enums\EstadoCarrera;
use App\Enums\EstadoGeneral;
use App\Exceptions\OperacionNoPermitida;
use App\Models\Carrera;
use App\Models\PlanEstudio;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * P1 Gestión de Plan de Estudio (entrada de CU 5–8) y CU 5 Registrar Plan de Estudio (Coordinador de Carrera).
 */
class PlanEstudioService
{
    /** CU 5 alt. 7.1. */
    public const MENSAJE_ANIO_INVALIDO = 'El año ingresado no es válido';

    /** Título de la Confirmación (Registrar_Plan_P2.png). */
    public const CONFIRMACION_REGISTRO = 'Plan de estudio registrado exitosamente';

    public const ANIO_MINIMO = 1990;

    /** @return Collection<int, Carrera> combo "Seleccionar Carrera" (E6-D2). */
    public function carrerasSeleccionables(): Collection
    {
        return Carrera::query()->gestionablesEnPlan()->orderBy('nombre_carrera')->get();
    }

    /** Carrera elegida en el combo; sin valor, la primera (el wireframe siempre muestra una elegida). */
    public function carreraElegida(Collection $carreras, ?int $id): ?Carrera
    {
        if ($id === null) {
            return $carreras->first();
        }

        return $carreras->firstWhere('id', $id) ?? abort(404);
    }

    /** @return Collection<int, PlanEstudio> combo "Año del Plan de Estudio" (E6-D4). */
    public function planesDe(Carrera $carrera): Collection
    {
        return $carrera->planesEstudio()->orderByDesc('anio_plan_estudio')->get();
    }

    /** Plan elegido en el combo; sin valor, el "Activo". Un plan de otra carrera da 404. */
    public function planElegido(Carrera $carrera, ?int $id): ?PlanEstudio
    {
        if ($id === null) {
            return $carrera->planActivo;
        }

        return $carrera->planesEstudio()->findOrFail($id);
    }

    /** CU 5 (E6-D3): carrera "Pendiente" o "Activo" sin Plan de Estudio activo (un solo plan activo por carrera, §7-28). */
    public function puedeRegistrarPlan(Carrera $carrera): bool
    {
        return in_array($carrera->estado_carrera, [EstadoCarrera::Pendiente, EstadoCarrera::Activo], true)
            && ! $carrera->planActivo()->exists();
    }

    /**
     * CU 5 pasos 11–12: Plan de Estudio en estado "Activo" vinculado a la Carrera.
     *
     * @throws OperacionNoPermitida si la carrera ya no admite un plan nuevo (E6-D9)
     */
    public function registrar(Carrera $carrera, int $anio): PlanEstudio
    {
        return DB::transaction(function () use ($carrera, $anio) {
            $carrera = Carrera::query()->lockForUpdate()->findOrFail($carrera->getKey());

            if (! $this->puedeRegistrarPlan($carrera)) {
                throw new OperacionNoPermitida('La carrera ya tiene un plan de estudio activo.');
            }

            $plan = new PlanEstudio(['anio_plan_estudio' => $anio, 'estado_plan_estudio' => EstadoGeneral::Activo]);
            $plan->carrera()->associate($carrera)->save();

            return $plan;
        });
    }

    /** @return list<string> */
    public function lineasConfirmacion(PlanEstudio $plan): array
    {
        return ["Carrera: {$plan->carrera->nombre_carrera}", "Año del Plan de Estudio: {$plan->anio_plan_estudio}"];
    }
}
