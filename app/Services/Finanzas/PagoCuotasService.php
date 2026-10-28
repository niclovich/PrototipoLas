<?php

namespace App\Services\Finanzas;

use App\Enums\EstadoGeneral;
use App\Exceptions\OperacionNoPermitida;
use App\Models\Alumno;
use App\Models\Beca;
use App\Models\Caja;
use App\Models\Cuota;
use App\Models\DetallePagoCuota;
use App\Models\FacturaComprobante;
use App\Models\Pago;
use App\Models\User;
use App\Services\Numeracion\NumeradorService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * CU7 "Registrar Pago de Cuotas" (colaboración Fig. 55). Un solo medio de pago por Pago (§7-5); la Beca "Activo"
 * vigente bonifica un porcentaje (`valor_beca`, §7-8). Las cuotas pendientes las calcula DeudaCuotasService.
 */
class PagoCuotasService
{
    /** Alt. 2.2. */
    public const MENSAJE_SIN_CAJA = 'No se puede registrar el pago porque no existe una caja abierta';

    /** Alt. 6.2. */
    public const MENSAJE_SIN_CUOTAS = 'El Alumno no posee cuotas pendientes de pago';

    /** Saldo Pendiente distinto de 0 (propio, §7-5). */
    public const MENSAJE_IMPORTE = 'El importe debe ser igual al Total a Pagar.';

    public const MENSAJE_CUOTAS = 'Seleccione con "Cobrar" al menos una cuota pendiente.';

    public const CONFIRMACION = 'Pago de Cuotas registrado exitosamente';

    public const CONFIRMACION_DETALLE = 'Las cuotas seleccionadas quedaron canceladas y se genero la factura correspondiente.';

    public function __construct(
        private readonly DeudaCuotasService $deuda,
        private readonly CajaService $cajas,
        private readonly NumeradorService $numerador,
    ) {}

    /**
     * Pasos 2 y 6: caja abierta del cajero y cuotas pendientes (si no, terminan el CU con su mensaje).
     *
     * @return Collection<int, Cuota>
     *
     * @throws OperacionNoPermitida
     */
    public function iniciar(Alumno $alumno, User $cajero): Collection
    {
        if (! $this->cajas->cajaAbiertaDe($cajero)) {
            throw new OperacionNoPermitida(self::MENSAJE_SIN_CAJA);
        }

        $pendientes = $this->deuda->cuotasPendientes($alumno);
        if ($pendientes->isEmpty()) {
            throw new OperacionNoPermitida(self::MENSAJE_SIN_CUOTAS);
        }

        return $pendientes;
    }

    /** Paso 9: Beca "Activo" del alumno, vigente hoy. */
    public function becaVigente(Alumno $alumno): ?Beca
    {
        return $alumno->becas()->activas()
            ->whereDate('fecha_inicio', '<=', today())->whereDate('fecha_fin', '>=', today())
            ->latest('fecha_inicio')->first();
    }

    /**
     * "Cuotas Pagadas" de P2: detalles de los Pagos "Activo" del alumno, con su factura.
     *
     * @return EloquentCollection<int, DetallePagoCuota>
     */
    public function cuotasPagadas(Alumno $alumno): EloquentCollection
    {
        return DetallePagoCuota::query()
            ->whereHas('pago', fn ($p) => $p->where('alumno_id', $alumno->id)->where('estado_pago', EstadoGeneral::Activo->value))
            ->with(['pago.factura', 'cuota.mes'])
            ->get()
            ->sortBy(fn (DetallePagoCuota $d) => [$d->pago->fecha_pago, $d->cuota->anio, $d->cuota->mes_id])
            ->values();
    }

    /** Fecha de "Cuotas a pagar": vencimiento (vacío para la Inscripción, §7-7). */
    public function vencimiento(Cuota $cuota): ?string
    {
        return $this->deuda->vencimiento($cuota)?->format('d/m/Y');
    }

    /**
     * Pasos 10–12: monto original, bonificación y total, y el reparto por cuota.
     *
     * @param  Collection<int, Cuota>  $cuotas
     * @return array{original: float, bonificacion: float, total: float, detalles: list<array{cuota: Cuota, bonificado: float, aplicado: float}>}
     */
    public function calcular(Collection $cuotas, ?Beca $beca): array
    {
        $porcentaje = $beca ? (float) $beca->valor_beca : 0.0;
        $detalles = $cuotas->map(function (Cuota $cuota) use ($porcentaje) {
            $bonificado = round((float) $cuota->monto * $porcentaje / 100, 2);

            return ['cuota' => $cuota, 'bonificado' => $bonificado, 'aplicado' => round((float) $cuota->monto - $bonificado, 2)];
        })->values()->all();

        $original = round((float) $cuotas->sum('monto'), 2);
        $bonificacion = round(array_sum(array_column($detalles, 'bonificado')), 2);

        return ['original' => $original, 'bonificacion' => $bonificacion, 'total' => round($original - $bonificacion, 2), 'detalles' => $detalles];
    }

    /**
     * Pasos 19–22.
     *
     * @param  list<int>  $cuotaIds
     *
     * @throws OperacionNoPermitida|ValidationException
     */
    public function registrar(Alumno $alumno, User $cajero, array $cuotaIds, int $medioPagoId, float $importe): Pago
    {
        return DB::transaction(function () use ($alumno, $cajero, $cuotaIds, $medioPagoId, $importe) {
            $caja = Caja::query()->whereKey($this->cajas->cajaAbiertaDe($cajero)?->id)->lockForUpdate()->first();
            if (! $caja) {
                throw new OperacionNoPermitida(self::MENSAJE_SIN_CAJA);
            }

            // Las cuotas deben seguir pendientes (otro pago pudo cancelarlas mientras tanto).
            Alumno::query()->whereKey($alumno->id)->lockForUpdate()->first();
            $cuotas = $this->deuda->cuotasPendientes($alumno)->whereIn('id', $cuotaIds)->values();
            if ($cuotas->isEmpty() || $cuotas->count() !== count(array_unique($cuotaIds))) {
                throw ValidationException::withMessages(['cuotas' => self::MENSAJE_CUOTAS]);
            }

            $beca = $this->becaVigente($alumno);
            $calculo = $this->calcular($cuotas, $beca);
            if (abs($calculo['total'] - round($importe, 2)) >= 0.005) {
                throw ValidationException::withMessages(['importe' => self::MENSAJE_IMPORTE]);
            }

            $pago = Pago::create([
                'alumno_id' => $alumno->id,
                'caja_id' => $caja->id,
                'medio_pago_id' => $medioPagoId,
                'fecha_pago' => now(),
                'monto_total' => $calculo['total'],
                'estado_pago' => EstadoGeneral::Activo,
            ]);

            foreach ($calculo['detalles'] as $detalle) {
                $pago->detalles()->create([
                    'cuota_id' => $detalle['cuota']->id,
                    'beca_id' => $detalle['bonificado'] > 0 ? $beca?->id : null,
                    'monto_aplicado' => $detalle['aplicado'],
                    'monto_bonificado' => $detalle['bonificado'],
                ]);
            }

            $pago->factura()->create([
                'numero_comprobante' => $this->numerador->siguiente((new FacturaComprobante)->getTable(), 'numero_comprobante'),
                'fecha_emision' => today(),
                'estado_factura' => EstadoGeneral::Activo,
            ]);

            return $pago;
        });
    }
}
