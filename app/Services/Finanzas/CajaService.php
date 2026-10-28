<?php

namespace App\Services\Finanzas;

use App\Enums\EstadoCaja;
use App\Enums\EstadoGeneral;
use App\Exceptions\OperacionNoPermitida;
use App\Models\Caja;
use App\Models\MedioPago;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * CU 35 "Registrar datos de Apertura de Caja" y CU 36 "Registrar datos de Cierre de Caja" (Cajero).
 * El cierre sigue la aclaración "Cálculo del cierre" (pp. 162–163).
 */
class CajaService
{
    public const MENSAJE_YA_TIENE_CAJA = 'Ya tenés una caja abierta';

    public const MENSAJE_MONTO_INICIAL = 'El monto inicial debe ser mayor a cero';

    public const MENSAJE_SIN_CAJA_PARA_CERRAR = 'No tenés ninguna caja abierta para cerrar';

    public const MENSAJE_MONTO_CONTADO = 'El monto contado debe ser un valor mayor o igual a cero';

    public const MENSAJE_OBSERVACION = 'Debe ingresar una observación que justifique la diferencia';

    public const CONFIRMACION_APERTURA = 'Apertura de Caja registrada exitosamente';

    public const CONFIRMACION_CIERRE = 'Cierre de Caja registrado exitosamente';

    /** CU 35 paso 2 / CU 36 paso 2 / CU7 paso 2. */
    public function cajaAbiertaDe(User $cajero): ?Caja
    {
        return $cajero->cajas()->where('estado_caja', EstadoCaja::Abierta->value)->latest('fecha_apertura')->first();
    }

    /** CU 35 pasos 2–9. */
    public function abrir(User $cajero, float $montoInicial): Caja
    {
        if ($montoInicial <= 0) {
            throw ValidationException::withMessages(['monto_inicial' => self::MENSAJE_MONTO_INICIAL]);
        }

        return DB::transaction(function () use ($cajero, $montoInicial) {
            User::query()->whereKey($cajero->id)->lockForUpdate()->first();
            if ($this->cajaAbiertaDe($cajero)) {
                throw new OperacionNoPermitida(self::MENSAJE_YA_TIENE_CAJA);
            }

            return $cajero->cajas()->create([
                'fecha_apertura' => now(),
                'monto_inicial' => $montoInicial,
                'estado_caja' => EstadoCaja::Abierta,
            ]);
        });
    }

    /** La caja se puede cerrar solo si está Abierta y es del Cajero logueado (CU 36 alt. 2). */
    public function puedeCerrar(Caja $caja, User $cajero): bool
    {
        return $caja->estado_caja === EstadoCaja::Abierta && (int) $caja->user_id === (int) $cajero->id;
    }

    /**
     * CU 36 pasos 3–5: por cada Medio de Pago vigente (y los que tengan pagos en la caja), el Monto Esperado =
     * Σ Pagos "Activo" de la caja con ese medio; el Efectivo suma el Monto Inicial. Para una caja Cerrada, el detalle registrado.
     *
     * @return Collection<int, array{medio: MedioPago, esperado: float, contado: float|null, diferencia: float|null}>
     */
    public function detalleCierre(Caja $caja): Collection
    {
        if ($caja->estado_caja === EstadoCaja::Cerrada) {
            return $caja->detallesCierre()->with('medioPago')->orderBy('medio_pago_id')->get()
                ->map(fn ($d) => ['medio' => $d->medioPago, 'esperado' => (float) $d->monto_esperado, 'contado' => (float) $d->monto_contado, 'diferencia' => (float) $d->diferencia]);
        }

        $recaudado = $caja->pagos()->where('estado_pago', EstadoGeneral::Activo->value)
            ->groupBy('medio_pago_id')->selectRaw('medio_pago_id, SUM(monto_total) AS total')->pluck('total', 'medio_pago_id');

        return MedioPago::query()->withTrashed()
            ->where(fn ($q) => $q->whereNull('deleted_at')->orWhereIn('id', $recaudado->keys()))
            ->orderBy('id')->get()
            ->map(fn (MedioPago $medio) => [
                'medio' => $medio,
                'esperado' => round((float) ($recaudado[$medio->id] ?? 0) + ($medio->medio_pago === MedioPago::EFECTIVO ? (float) $caja->monto_inicial : 0), 2),
                'contado' => null,
                'diferencia' => null,
            ]);
    }

    /**
     * CU 36 pasos 6–18.
     *
     * @param  array<int|string, mixed>  $contados  medio_pago_id => monto contado
     */
    public function cerrar(Caja $caja, User $cajero, array $contados, ?string $observacion): Caja
    {
        return DB::transaction(function () use ($caja, $cajero, $contados, $observacion) {
            $caja = Caja::query()->lockForUpdate()->findOrFail($caja->id);
            if (! $this->puedeCerrar($caja, $cajero)) {
                throw new OperacionNoPermitida(self::MENSAJE_SIN_CAJA_PARA_CERRAR);
            }

            $filas = $this->detalleCierre($caja)->map(function (array $fila) use ($contados) {
                $contado = $contados[$fila['medio']->id] ?? null;
                if (! is_numeric($contado) || (float) $contado < 0) {
                    throw ValidationException::withMessages(["contados.{$fila['medio']->id}" => self::MENSAJE_MONTO_CONTADO]);
                }

                return [...$fila, 'contado' => round((float) $contado, 2), 'diferencia' => round((float) $contado - $fila['esperado'], 2)];
            });

            $diferenciaTotal = round($filas->sum('diferencia'), 2);
            if ($filas->contains(fn ($f) => $f['diferencia'] != 0) && trim((string) $observacion) === '') {
                throw ValidationException::withMessages(['observacion_cierre' => self::MENSAJE_OBSERVACION]);
            }

            foreach ($filas as $fila) {
                $caja->detallesCierre()->create([
                    'medio_pago_id' => $fila['medio']->id,
                    'monto_esperado' => $fila['esperado'],
                    'monto_contado' => $fila['contado'],
                    'diferencia' => $fila['diferencia'],
                ]);
            }

            $caja->update([
                'estado_caja' => EstadoCaja::Cerrada,
                'fecha_cierre' => now(),
                'monto_final' => round($filas->sum('contado'), 2),
                'diferencia_total' => $diferenciaTotal,
                'observacion_cierre' => trim((string) $observacion) ?: null,
            ]);

            return $caja;
        });
    }
}
