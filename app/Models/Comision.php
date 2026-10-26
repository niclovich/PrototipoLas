<?php

namespace App\Models;

use App\Enums\EstadoGeneral;
use App\Models\Concerns\AuditaCambios;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Clase Comision (Hoja 2). Comision 1..* — 1..1 Carrera / Modalidad; 1..* DetComision. */
class Comision extends Model
{
    use AuditaCambios, HasFactory;

    protected $table = 'comisiones';

    protected $primaryKey = 'id';

    protected $fillable = [
        'carrera_id', 'modalidad_id', 'codigo_comision', 'capacidad_comision', 'carga_horaria_comision',
        'horario_comision', 'anio_curso', 'anio_lectivo', 'estado_comision',
    ];

    protected function casts(): array
    {
        return [
            'capacidad_comision' => 'integer',
            'carga_horaria_comision' => 'integer',
            'anio_curso' => 'integer',
            'anio_lectivo' => 'integer',
            'estado_comision' => EstadoGeneral::class,
        ];
    }

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class);
    }

    public function modalidad(): BelongsTo
    {
        return $this->belongsTo(Modalidad::class)->withTrashed();
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetComision::class);
    }

    /** Alumnos que "cursan actualmente en" esta comisión (0..1 desde Alumno). */
    public function alumnosActuales(): HasMany
    {
        return $this->hasMany(Alumno::class, 'comision_actual_id');
    }
}
