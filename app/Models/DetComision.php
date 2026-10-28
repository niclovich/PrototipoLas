<?php

namespace App\Models;

use App\Enums\EstadoGeneral;
use App\Models\Concerns\AuditaCambios;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Clase DetComision (Hoja 2): materia del plan dictada en una comisión. */
class DetComision extends Model
{
    use AuditaCambios, HasFactory;

    protected $table = 'det_comisiones';

    protected $primaryKey = 'id';

    protected $fillable = ['comision_id', 'detalle_plan_estudio_id', 'estado_det_comision'];

    protected function casts(): array
    {
        return ['estado_det_comision' => EstadoGeneral::class];
    }

    /** "Comisiones dictándose" (CU 7 paso 6, E7-D3): DetComision "Activo" de una Comisión "Activo". */
    public function scopeDictandose(Builder $query): Builder
    {
        return $query->where('estado_det_comision', EstadoGeneral::Activo->value)
            ->whereHas('comision', fn (Builder $comision) => $comision->where('estado_comision', EstadoGeneral::Activo->value));
    }

    public function comision(): BelongsTo
    {
        return $this->belongsTo(Comision::class);
    }

    public function detallePlan(): BelongsTo
    {
        return $this->belongsTo(DetallePlanEstudio::class, 'detalle_plan_estudio_id');
    }

    public function inscripciones(): HasMany
    {
        return $this->hasMany(AlumnoComision::class);
    }
}
