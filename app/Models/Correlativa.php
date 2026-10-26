<?php

namespace App\Models;

use App\Enums\CondicionCorrelativa;
use App\Models\Concerns\AuditaCambios;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Clase Correlativa (Hoja 2): dos asociaciones a DetallePlanEstudio (la que exige y la exigida). */
class Correlativa extends Model
{
    use AuditaCambios, HasFactory;

    protected $table = 'correlativas';

    protected $primaryKey = 'id';

    protected $fillable = ['detalle_plan_estudio_id', 'detalle_plan_correlativa_id', 'p_cursar', 'p_rendir'];

    protected function casts(): array
    {
        return ['p_cursar' => CondicionCorrelativa::class, 'p_rendir' => CondicionCorrelativa::class];
    }

    public function detallePlan(): BelongsTo
    {
        return $this->belongsTo(DetallePlanEstudio::class, 'detalle_plan_estudio_id');
    }

    public function detallePlanCorrelativa(): BelongsTo
    {
        return $this->belongsTo(DetallePlanEstudio::class, 'detalle_plan_correlativa_id');
    }
}
