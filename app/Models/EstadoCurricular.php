<?php

namespace App\Models;

use App\Enums\CondicionCurricular;
use App\Models\Concerns\AuditaCambios;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Clase EstadoCurricular (Hoja 2): historial de condición por materia (CU4 siempre crea uno; CU6 con nroActa). */
class EstadoCurricular extends Model
{
    use AuditaCambios, HasFactory;

    protected $table = 'estados_curriculares';

    protected $primaryKey = 'id';

    protected $fillable = ['alumno_id', 'detalle_plan_estudio_id', 'condicion', 'fecha_condicion', 'nota_condicion', 'nro_acta'];

    protected function casts(): array
    {
        return [
            'condicion' => CondicionCurricular::class,
            'fecha_condicion' => 'date',
            'nota_condicion' => 'decimal:2',
            'nro_acta' => 'integer',
        ];
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class);
    }

    public function detallePlan(): BelongsTo
    {
        return $this->belongsTo(DetallePlanEstudio::class, 'detalle_plan_estudio_id');
    }
}
