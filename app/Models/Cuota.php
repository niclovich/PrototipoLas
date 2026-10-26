<?php

namespace App\Models;

use App\Enums\EstadoGeneral;
use App\Models\Concerns\AuditaCambios;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Clase Cuota (Hoja 4): una cuota por Carrera, año lectivo y Mes (CU 33/34). El vencimiento lo calcula DeudaCuotasService. */
class Cuota extends Model
{
    use AuditaCambios, HasFactory;

    protected $table = 'cuotas';

    protected $primaryKey = 'id';

    protected $fillable = ['carrera_id', 'mes_id', 'monto', 'anio', 'estado_cuota'];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'anio' => 'integer',
            'estado_cuota' => EstadoGeneral::class,
        ];
    }

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class);
    }

    public function mes(): BelongsTo
    {
        return $this->belongsTo(Mes::class);
    }

    public function detallesPago(): HasMany
    {
        return $this->hasMany(DetallePagoCuota::class);
    }

    public function scopeDeInscripcion(Builder $query): Builder
    {
        return $query->whereHas('mes', fn (Builder $mes) => $mes->where('descripcion', Mes::INSCRIPCION));
    }

    public function scopeMensuales(Builder $query): Builder
    {
        return $query->whereHas('mes', fn (Builder $mes) => $mes->where('descripcion', '!=', Mes::INSCRIPCION));
    }

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('estado_cuota', EstadoGeneral::Activo->value);
    }
}
