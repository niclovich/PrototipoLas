<?php

namespace App\Models;

use App\Enums\EstadoGeneral;
use App\Models\Concerns\AuditaCambios;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Clase DetallePlanEstudio (Hoja 2): materia dentro de un plan (código, carga horaria, año de cursado). */
class DetallePlanEstudio extends Model
{
    use AuditaCambios, HasFactory;

    protected $table = 'detalles_plan_estudio';

    protected $primaryKey = 'id';

    protected $fillable = [
        'plan_estudio_id', 'materia_id', 'regimen_id', 'cuatrimestre_id', 'codigo_det_plan_estudio',
        'carga_horaria_det_plan_estudio', 'anio_curso_det_plan_estudio', 'estado_det_plan_estudio', 'fecha_baja',
    ];

    protected function casts(): array
    {
        return [
            'carga_horaria_det_plan_estudio' => 'integer',
            'anio_curso_det_plan_estudio' => 'integer',
            'estado_det_plan_estudio' => EstadoGeneral::class,
            'fecha_baja' => 'date',
        ];
    }

    public function planEstudio(): BelongsTo
    {
        return $this->belongsTo(PlanEstudio::class);
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class);
    }

    public function regimen(): BelongsTo
    {
        return $this->belongsTo(Regimen::class)->withTrashed();
    }

    public function cuatrimestre(): BelongsTo
    {
        return $this->belongsTo(Cuatrimestre::class)->withTrashed();
    }

    /** Correlatividades que exige esta materia. */
    public function correlativas(): HasMany
    {
        return $this->hasMany(Correlativa::class, 'detalle_plan_estudio_id');
    }

    /** Materias que la exigen como correlativa. */
    public function esCorrelativaDe(): HasMany
    {
        return $this->hasMany(Correlativa::class, 'detalle_plan_correlativa_id');
    }

    public function detComisiones(): HasMany
    {
        return $this->hasMany(DetComision::class);
    }

    public function actas(): HasMany
    {
        return $this->hasMany(ActaExamen::class);
    }

    public function estadosCurriculares(): HasMany
    {
        return $this->hasMany(EstadoCurricular::class);
    }
}
