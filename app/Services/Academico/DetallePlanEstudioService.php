<?php

namespace App\Services\Academico;

use App\Enums\EstadoGeneral;
use App\Exceptions\OperacionNoPermitida;
use App\Models\Cuatrimestre;
use App\Models\DetallePlanEstudio;
use App\Models\Materia;
use App\Models\PlanEstudio;
use App\Models\Regimen;
use Illuminate\Support\Facades\DB;

/**
 * CU 6 Registrar Materia al Plan de Estudio y CU 7 Registrar Baja de Materia del Plan (Coordinador de Carrera).
 */
class DetallePlanEstudioService
{
    /** CU 6 alt. 8.1. */
    public const MENSAJE_MATERIA_YA_CARGADA = 'La materia ya se encuentra cargada en el Plan de Estudio';

    /** CU 6 alt. 11.1. */
    public const MENSAJE_CODIGO_EXISTENTE = 'Código ya existente en el Plan de Estudio';

    /** CU 6 alt. 23.1. */
    public const MENSAJE_CARGA_INVALIDA = 'La carga horaria ingresada no es válida';

    /** CU 7 alt. 6.1. */
    public const MENSAJE_COMISIONES_DICTANDOSE = 'No se puede dar de baja la materia porque posee comisiones dictándose';

    /** E7-D4 (texto propio, sin escenario en la especificación). */
    public const MENSAJE_CUATRIMESTRE_REGIMEN = 'El cuatrimestre no corresponde al régimen seleccionado.';

    /** Confirmaciones (Registrar_Materia_P3.png; la de baja no tiene PNG, E7-D7). */
    public const CONFIRMACION_REGISTRO = 'Materia agregada exitosamente';

    public const CONFIRMACION_BAJA = 'Materia dada de baja exitosamente';

    /** Régimen y Cuatrimestre "Anual" van juntos (E7-D4). */
    public const ANUAL = 'Anual';

    public const CARGA_MAXIMA = 999;

    /** @return array<int, string> combo "Materia": todas las materias "Activo" (E7-D2). */
    public function materiasSeleccionables(): array
    {
        return Materia::query()->activas()->orderBy('nombre_materia')->pluck('nombre_materia', 'id')->all();
    }

    /** @return array<int, string> */
    public function regimenes(): array
    {
        return Regimen::query()->orderBy('id')->pluck('regimen', 'id')->all();
    }

    /** @return array<int, string> */
    public function cuatrimestres(): array
    {
        return Cuatrimestre::query()->orderBy('id')->pluck('cuatrimestre', 'id')->all();
    }

    /** @return array<int, int> combo "Año de Dictado": 1 … años de duración de la carrera (pasos 18–19). */
    public function aniosDeDictado(PlanEstudio $plan): array
    {
        $anios = range(1, max(1, (int) $plan->carrera->duracion?->cantidad_anios));

        return array_combine($anios, $anios);
    }

    /** E7-D4: régimen "Anual" ⇔ cuatrimestre "Anual". */
    public function cuatrimestreCorrespondeAlRegimen(int $regimenId, int $cuatrimestreId): bool
    {
        $regimenAnual = Regimen::query()->whereKey($regimenId)->value('regimen') === self::ANUAL;
        $cuatrimestreAnual = Cuatrimestre::query()->whereKey($cuatrimestreId)->value('cuatrimestre') === self::ANUAL;

        return $regimenAnual === $cuatrimestreAnual;
    }

    /**
     * CU 6 pasos 26–27: DetallePlanEstudio "Activo" vinculado al Plan, la Materia, el Régimen y el Cuatrimestre.
     *
     * @param  array<string, mixed>  $datos  validados por RegistrarMateriaPlanRequest
     */
    public function registrar(PlanEstudio $plan, array $datos): DetallePlanEstudio
    {
        return DB::transaction(function () use ($plan, $datos) {
            $plan = PlanEstudio::query()->activos()->lockForUpdate()->findOrFail($plan->getKey());

            $detalle = new DetallePlanEstudio([
                'codigo_det_plan_estudio' => $datos['codigo_det_plan_estudio'],
                'carga_horaria_det_plan_estudio' => (int) $datos['carga_horaria_det_plan_estudio'],
                'anio_curso_det_plan_estudio' => (int) $datos['anio_curso_det_plan_estudio'],
                'estado_det_plan_estudio' => EstadoGeneral::Activo,
            ]);
            $detalle->materia()->associate($datos['materia_id']);
            $detalle->regimen()->associate($datos['regimen_id']);
            $detalle->cuatrimestre()->associate($datos['cuatrimestre_id']);
            $detalle->planEstudio()->associate($plan)->save();

            return $detalle;
        });
    }

    /** CU 7 paso 6. */
    public function tieneComisionesDictandose(DetallePlanEstudio $detalle): bool
    {
        return $detalle->detComisiones()->dictandose()->exists();
    }

    /**
     * CU 7 pasos 6, 9: el detalle queda "Inactivo" con su fecha de baja (se conserva el historial).
     *
     * @throws OperacionNoPermitida alt. 6 (comisiones dictándose)
     */
    public function registrarBaja(DetallePlanEstudio $detalle): DetallePlanEstudio
    {
        return DB::transaction(function () use ($detalle) {
            $detalle = DetallePlanEstudio::query()->lockForUpdate()->findOrFail($detalle->getKey());

            if ($this->tieneComisionesDictandose($detalle)) {
                throw new OperacionNoPermitida(self::MENSAJE_COMISIONES_DICTANDOSE);
            }

            $detalle->update(['estado_det_plan_estudio' => EstadoGeneral::Inactivo, 'fecha_baja' => today()]);

            return $detalle;
        });
    }

    /** @return list<string> Registrar_Materia_P3.png */
    public function lineasRegistro(DetallePlanEstudio $detalle): array
    {
        $detalle->loadMissing(['planEstudio.carrera', 'materia', 'regimen', 'cuatrimestre']);

        return [
            "Carrera: {$detalle->planEstudio->carrera->nombre_carrera}",
            "Materia: {$detalle->materia->nombre_materia} · Código: {$detalle->codigo_det_plan_estudio}",
            "Régimen: {$detalle->regimen->regimen} · Cuatrimestre: {$detalle->cuatrimestre->abreviatura} · Carga Horaria: {$detalle->carga_horaria_det_plan_estudio} hs",
        ];
    }

    /** @return list<string> */
    public function lineasBaja(DetallePlanEstudio $detalle): array
    {
        $detalle->loadMissing(['planEstudio.carrera', 'materia']);

        return [
            "Carrera: {$detalle->planEstudio->carrera->nombre_carrera}",
            "Materia: {$detalle->materia->nombre_materia} · Código: {$detalle->codigo_det_plan_estudio}",
            'Fecha de Baja: '.$detalle->fecha_baja->format('d/m/Y'),
        ];
    }
}
