<?php

namespace App\Models;

use App\Enums\EstadoGeneral;
use App\Models\Concerns\AuditaCambios;
use Illuminate\Database\Eloquent\Builder;
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

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('estado_comision', EstadoGeneral::Activo->value);
    }

    /** "1er Anio", "2do Anio", … (texto de los wireframes de CU3 y CU4). */
    public function anioCursoTexto(): string
    {
        $sufijo = match ($this->anio_curso) {
            1, 3 => 'er',
            2 => 'do',
            7, 10 => 'mo',
            8 => 'vo',
            9 => 'no',
            default => 'to',
        };

        return "{$this->anio_curso}{$sufijo} Anio";
    }

    /** "COM-2026-1A · 1er Anio · Turno Manana" (combos de CU3) y, con modalidad, "… · Presencial" (Comision Actual del cambio). */
    public function etiqueta(bool $conModalidad = false): string
    {
        $partes = [$this->codigo_comision, $this->anioCursoTexto(), $this->horario_comision];
        if ($conModalidad) {
            $partes[] = $this->modalidad?->modalidad;
        }

        return implode(' · ', array_filter($partes));
    }

    /** Alumnos que "cursan actualmente en" esta comisión (0..1 desde Alumno). */
    public function alumnosActuales(): HasMany
    {
        return $this->hasMany(Alumno::class, 'comision_actual_id');
    }
}
