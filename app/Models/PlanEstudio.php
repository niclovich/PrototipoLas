<?php

namespace App\Models;

use App\Enums\EstadoGeneral;
use App\Models\Concerns\AuditaCambios;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Clase PlanEstudio (Hoja 2). PlanEstudio 1..* — 1..1 Carrera; 1..* DetallePlanEstudio. */
class PlanEstudio extends Model
{
    use AuditaCambios, HasFactory;

    protected $table = 'planes_estudio';

    protected $primaryKey = 'id';

    protected $fillable = ['carrera_id', 'anio_plan_estudio', 'estado_plan_estudio'];

    protected function casts(): array
    {
        return ['anio_plan_estudio' => 'integer', 'estado_plan_estudio' => EstadoGeneral::class];
    }

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePlanEstudio::class);
    }

    public function alumnos(): HasMany
    {
        return $this->hasMany(Alumno::class);
    }
}
