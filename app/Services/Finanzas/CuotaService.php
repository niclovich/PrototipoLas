<?php

namespace App\Services\Finanzas;

use App\Enums\EstadoGeneral;
use App\Exceptions\OperacionNoPermitida;
use App\Models\Carrera;
use App\Models\Cuota;
use App\Models\Mes;
use App\Support\Moneda;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * CU 33 Registrar y CU 34 Modificar datos de Cuotas por Carrera (Director General / Coordinador).
 * El vencimiento no se guarda: lo calcula DeudaCuotasService a partir del Mes y el año.
 */
class CuotaService
{
    /** CU 33 alt. 7.1, 10.1 y 13.1; CU 34 alt. 7.1, 11.1 y 15.1. */
    public const MENSAJE_ANIO_MENOR = 'El año lectivo no puede ser menor al año actual';

    public const MENSAJE_CUOTA_EXISTENTE = 'Ya existe una cuota activa para esta combinación de carrera, año lectivo y mes';

    public const MENSAJE_MONTO_INVALIDO = 'El monto ingresado no es válido';

    public const MENSAJE_ANIO_ANTERIOR = 'No se pueden modificar cuotas de años lectivos anteriores';

    public const MENSAJE_CON_PAGOS = 'La cuota no puede modificarse porque posee pagos registrados';

    public const CONFIRMACION_REGISTRO = 'Cuota registrada con éxito';

    public const CONFIRMACION_MODIFICACION = 'Cuota modificada con éxito';

    public const MONTO_MAXIMO = 9999999999.99;

    /** @return Collection<int, Carrera> combo "Seleccionar Carrera" (paso 2: "Pendiente" o "Activo"). */
    public function carreras(): Collection
    {
        return Carrera::query()->gestionablesEnPlan()->orderBy('nombre_carrera')->get(['id', 'nombre_carrera']);
    }

    /** @return list<int> combo "Año Lectivo": años con cuotas de la carrera, el actual y el siguiente. */
    public function aniosLectivos(?Carrera $carrera): array
    {
        $actual = self::anioActual();

        return collect($carrera ? $carrera->cuotas()->distinct()->pluck('anio')->all() : [])
            ->merge([$actual, $actual + 1])
            ->map(fn ($anio) => (int) $anio)
            ->unique()->sort()->values()->all();
    }

    /** @return array<int, string> Mes (precargados, en orden) */
    public function meses(): array
    {
        return Mes::query()->orderBy('id')->pluck('descripcion', 'id')->all();
    }

    /**
     * CU 33 pasos 10, 16–17: valida la unicidad entre cuotas "Activo" (bloqueando la carrera) y registra la cuota.
     *
     * @param  array{mes_id: int, anio: int, monto: string|float}  $datos
     */
    public function registrar(Carrera $carrera, array $datos): Cuota
    {
        return DB::transaction(function () use ($carrera, $datos) {
            Carrera::query()->lockForUpdate()->findOrFail($carrera->getKey());

            $existe = Cuota::query()->activas()
                ->where('carrera_id', $carrera->id)
                ->where('anio', $datos['anio'])
                ->where('mes_id', $datos['mes_id'])
                ->exists();
            if ($existe) {
                throw ValidationException::withMessages(['mes_id' => self::MENSAJE_CUOTA_EXISTENTE]);
            }

            $cuota = new Cuota(['monto' => $datos['monto'], 'anio' => $datos['anio'], 'estado_cuota' => EstadoGeneral::Activo]);
            $cuota->carrera()->associate($carrera);
            $cuota->mes()->associate($datos['mes_id']);
            $cuota->save();

            return $cuota;
        });
    }

    /**
     * CU 34 pasos 7 y 11: la cuota es de un año lectivo vigente y no tiene pagos registrados.
     *
     * @throws OperacionNoPermitida con el mensaje del alternativo
     */
    public function asegurarModificable(Cuota $cuota): void
    {
        if ($cuota->anio < self::anioActual()) {
            throw new OperacionNoPermitida(self::MENSAJE_ANIO_ANTERIOR);
        }
        if ($this->poseePagos($cuota)) {
            throw new OperacionNoPermitida(self::MENSAJE_CON_PAGOS);
        }
    }

    /** CU 34 paso 19: solo cambia el monto; la cuota sigue "Activo" y vinculada a la carrera. Devuelve el monto anterior. */
    public function modificar(Cuota $cuota, string|float $monto): string
    {
        return DB::transaction(function () use ($cuota, $monto) {
            $cuota = Cuota::query()->lockForUpdate()->findOrFail($cuota->getKey());
            $this->asegurarModificable($cuota);
            $anterior = (string) $cuota->monto;
            $cuota->monto = $monto;
            $cuota->save();

            return $anterior;
        });
    }

    /** E10-D4: DetallePagoCuota de un Pago "Activo". */
    public function poseePagos(Cuota $cuota): bool
    {
        return $cuota->detallesPago()
            ->whereHas('pago', fn ($pago) => $pago->where('estado_pago', EstadoGeneral::Activo->value))
            ->exists();
    }

    /** @return list<string> */
    public function lineasConfirmacion(Cuota $cuota, ?string $montoAnterior = null): array
    {
        $cuota->loadMissing(['carrera', 'mes']);

        return [
            "Carrera: {$cuota->carrera->nombre_carrera}",
            "Año Lectivo: {$cuota->anio} · Mes: {$cuota->mes->descripcion}",
            $montoAnterior === null
                ? 'Monto: '.Moneda::formatear($cuota->monto)
                : 'Monto anterior: '.Moneda::formatear($montoAnterior).' · Monto nuevo: '.Moneda::formatear($cuota->monto),
        ];
    }

    public static function anioActual(): int
    {
        return (int) now()->format('Y');
    }
}
