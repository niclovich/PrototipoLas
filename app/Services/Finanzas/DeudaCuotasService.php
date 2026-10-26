<?php

namespace App\Services\Finanzas;

use App\Enums\EstadoGeneral;
use App\Models\Alumno;
use App\Models\Cuota;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Único lugar donde se calcula vencimiento y deuda de cuotas (Aclaración "Meses y vencimiento de las Cuotas", CU 33).
 * Lo usan CU2, CU3, CU5, CU6, Pago de Cuotas, Notificar Deuda y Morosidad.
 *
 * - Las cuotas de Marzo a Diciembre vencen el día LAS_CUOTA_DIA_VENCIMIENTO de su mes (se calcula, no se guarda).
 * - La cuota de Inscripción no vence y nunca cuenta como vencida.
 * - Una cuota está cancelada si tiene un DetallePagoCuota de un Pago "Activo" del alumno.
 * - Supuesto de la tesis: el alumno debe todas las cuotas mensuales desde el año lectivo de la Inscripción que pagó.
 */
class DeudaCuotasService
{
    public function vencimiento(Cuota $cuota): ?CarbonImmutable
    {
        $mes = $cuota->mes->numeroCalendario();

        return $mes === null
            ? null
            : CarbonImmutable::create($cuota->anio, $mes, config('las.cuota_dia_vencimiento'))->endOfDay();
    }

    public function estaVencida(Cuota $cuota, Alumno $alumno, ?CarbonInterface $hoy = null): bool
    {
        $vencimiento = $this->vencimiento($cuota);

        return $vencimiento !== null
            && ($hoy ?? now())->greaterThan($vencimiento)
            && ! $this->estaCancelada($cuota, $alumno);
    }

    public function estaCancelada(Cuota $cuota, Alumno $alumno): bool
    {
        return $cuota->detallesPago()->whereHas('pago', fn (Builder $pago) => $this->pagoActivoDe($pago, $alumno))->exists();
    }

    public function cuotaInscripcion(Alumno $alumno, int $anio): ?Cuota
    {
        return $this->cuotasDeLaCarrera($alumno)->deInscripcion()->where('anio', $anio)->first();
    }

    /** CU2 paso 19: la Cuota de Inscripción del año lectivo debe estar pagada. */
    public function inscripcionPagada(Alumno $alumno, int $anio): bool
    {
        $cuota = $this->cuotaInscripcion($alumno, $anio);

        return $cuota !== null && $this->estaCancelada($cuota, $alumno);
    }

    /** Año lectivo de la primera Inscripción pagada; null si el alumno todavía no pagó ninguna (preinscripto). */
    public function anioIngreso(Alumno $alumno): ?int
    {
        $anio = $this->cuotasDeLaCarrera($alumno)
            ->deInscripcion()
            ->whereHas('detallesPago.pago', fn (Builder $pago) => $this->pagoActivoDe($pago, $alumno))
            ->min('anio');

        return $anio === null ? null : (int) $anio;
    }

    /**
     * Pago de Cuotas, paso 6: cuotas pendientes de pago. Sin Inscripción pagada, solo la Inscripción del año en curso
     * en adelante (un preinscripto puede pagarla); después, las mensuales desde el año de ingreso.
     *
     * @return Collection<int, Cuota>
     */
    public function cuotasPendientes(Alumno $alumno): Collection
    {
        $anioIngreso = $this->anioIngreso($alumno);

        $query = $anioIngreso === null
            ? $this->cuotasDeLaCarrera($alumno)->deInscripcion()->where('anio', '>=', now()->year)
            : $this->cuotasDeLaCarrera($alumno)->mensuales()->where('anio', '>=', $anioIngreso);

        return $query->whereDoesntHave('detallesPago.pago', fn (Builder $pago) => $this->pagoActivoDe($pago, $alumno))
            ->with('mes')
            ->orderBy('anio')
            ->orderBy('mes_id')
            ->get();
    }

    /**
     * Cuotas mensuales vencidas e impagas (controles de CU3, CU5 y CU6, nivel de deuda y morosidad). Nunca incluye la Inscripción.
     *
     * @return Collection<int, Cuota>
     */
    public function cuotasVencidas(Alumno $alumno, ?CarbonInterface $hoy = null): Collection
    {
        if ($this->anioIngreso($alumno) === null) {
            return collect();
        }

        $hoy ??= now();

        return $this->cuotasPendientes($alumno)
            ->filter(fn (Cuota $cuota) => $hoy->greaterThan($this->vencimiento($cuota)))
            ->values();
    }

    public function tieneCuotasVencidas(Alumno $alumno, ?CarbonInterface $hoy = null): bool
    {
        return $this->cuotasVencidas($alumno, $hoy)->isNotEmpty();
    }

    /** @return Builder<Cuota> */
    private function cuotasDeLaCarrera(Alumno $alumno): Builder
    {
        return Cuota::query()->activas()->where('carrera_id', $alumno->carrera_id);
    }

    private function pagoActivoDe(Builder $pago, Alumno $alumno): Builder
    {
        return $pago->where('alumno_id', $alumno->id)->where('estado_pago', EstadoGeneral::Activo->value);
    }
}
