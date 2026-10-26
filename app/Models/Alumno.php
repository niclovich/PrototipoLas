<?php

namespace App\Models;

use App\Enums\EstadoAlumno;
use App\Enums\EstadoLegajo;
use App\Enums\SituacionEstudios;
use App\Models\Concerns\AuditaCambios;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Clase Alumno (Hoja 2). El legajo es por carrera: un Usuario tiene un Alumno por carrera.
 * Alumno 1..* "cursa" 1..1 PlanEstudio; "cursa actualmente en" 0..1 Comision.
 */
class Alumno extends Model
{
    use AuditaCambios, HasFactory;

    protected $table = 'alumnos';

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id', 'carrera_id', 'plan_estudio_id', 'comision_actual_id', 'nro_legajo', 'estado_alumno',
        'estado_legajo', 'situacion_estudios', 'adeuda_materia', 'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'nro_legajo' => 'integer',
            'estado_alumno' => EstadoAlumno::class,
            'estado_legajo' => EstadoLegajo::class,
            'situacion_estudios' => SituacionEstudios::class,
            'adeuda_materia' => 'boolean',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class);
    }

    public function planEstudio(): BelongsTo
    {
        return $this->belongsTo(PlanEstudio::class);
    }

    public function comisionActual(): BelongsTo
    {
        return $this->belongsTo(Comision::class, 'comision_actual_id');
    }

    public function inscripcionesComision(): HasMany
    {
        return $this->hasMany(AlumnoComision::class);
    }

    public function estadosCurriculares(): HasMany
    {
        return $this->hasMany(EstadoCurricular::class);
    }

    public function detallesActa(): HasMany
    {
        return $this->hasMany(DetalleActa::class);
    }

    public function tituloTramite(): HasOne
    {
        return $this->hasOne(TituloTramite::class);
    }

    public function novedades(): HasMany
    {
        return $this->hasMany(Novedad::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }

    public function solicitudesBeca(): HasMany
    {
        return $this->hasMany(SolicitudBeca::class);
    }

    public function becas(): HasMany
    {
        return $this->hasMany(Beca::class);
    }

    public function scopeConLegajoActivo(Builder $query): Builder
    {
        return $query->where('estado_legajo', EstadoLegajo::Activo->value);
    }
}
