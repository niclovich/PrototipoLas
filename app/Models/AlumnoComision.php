<?php

namespace App\Models;

use App\Enums\CondicionCursada;
use App\Enums\EstadoAlumnoComision;
use App\Enums\TipoInscripcion;
use App\Models\Concerns\AuditaCambios;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Clase AlumnoComision (Hoja 2): inscripción del alumno a una materia de una comisión (CU3, CU4). */
class AlumnoComision extends Model
{
    use AuditaCambios, HasFactory;

    protected $table = 'alumno_comision';

    protected $primaryKey = 'id';

    protected $fillable = [
        'alumno_id', 'det_comision_id', 'fecha_inscripcion', 'estado_alumno_comision', 'tipo_inscripcion',
        'condicion_final', 'nota_p1', 'nota_p2', 'nota_r1', 'nota_r2', 'faltas_alumno',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inscripcion' => 'date',
            'estado_alumno_comision' => EstadoAlumnoComision::class,
            'tipo_inscripcion' => TipoInscripcion::class,
            'condicion_final' => CondicionCursada::class,
            'nota_p1' => 'decimal:2',
            'nota_p2' => 'decimal:2',
            'nota_r1' => 'decimal:2',
            'nota_r2' => 'decimal:2',
            'faltas_alumno' => 'integer',
        ];
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class);
    }

    public function detComision(): BelongsTo
    {
        return $this->belongsTo(DetComision::class);
    }

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('estado_alumno_comision', EstadoAlumnoComision::Activo->value);
    }
}
