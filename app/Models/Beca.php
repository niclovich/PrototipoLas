<?php

namespace App\Models;

use App\Enums\EstadoGeneral;
use App\Models\Concerns\AuditaCambios;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Clase Beca (Hoja 4). valor_beca = porcentaje de bonificación (plan §7-8, pendiente de confirmar). */
class Beca extends Model
{
    use AuditaCambios, HasFactory;

    protected $table = 'becas';

    protected $primaryKey = 'id';

    protected $fillable = [
        'alumno_id', 'convocatoria_beca_id', 'solicitud_beca_id', 'fecha_inicio', 'fecha_fin',
        'valor_beca', 'estado_beca', 'motivo_baja',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'valor_beca' => 'decimal:2',
            'estado_beca' => EstadoGeneral::class,
        ];
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class);
    }

    public function convocatoria(): BelongsTo
    {
        return $this->belongsTo(ConvocatoriaBeca::class, 'convocatoria_beca_id');
    }

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(SolicitudBeca::class, 'solicitud_beca_id');
    }

    public function detallesPago(): HasMany
    {
        return $this->hasMany(DetallePagoCuota::class);
    }

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('estado_beca', EstadoGeneral::Activo->value);
    }
}
