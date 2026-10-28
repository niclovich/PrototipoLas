<?php

namespace App\Models;

use App\Enums\EstadoCarrera;
use App\Enums\EstadoGeneral;
use App\Models\Concerns\AuditaCambios;
use App\Models\Concerns\BuscaPorPalabras;
use App\Rules\ResolucionMinisterial;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/** Clase Carrera (Hoja 2). Carrera 1..* — 1..1 Modalidad / Duracion. */
class Carrera extends Model
{
    use AuditaCambios, BuscaPorPalabras, HasFactory;

    protected $table = 'carreras';

    protected $primaryKey = 'id';

    protected $fillable = ['nombre_carrera', 'resolucion_carrera', 'estado_carrera', 'modalidad_id', 'duracion_id'];

    protected function casts(): array
    {
        return ['estado_carrera' => EstadoCarrera::class];
    }

    public function modalidad(): BelongsTo
    {
        return $this->belongsTo(Modalidad::class)->withTrashed();
    }

    public function duracion(): BelongsTo
    {
        return $this->belongsTo(Duracion::class)->withTrashed();
    }

    public function planesEstudio(): HasMany
    {
        return $this->hasMany(PlanEstudio::class);
    }

    /** Un solo plan Activo por carrera (validado en el Service, plan §7-28). */
    public function planActivo(): HasOne
    {
        return $this->hasOne(PlanEstudio::class)->where('estado_plan_estudio', EstadoGeneral::Activo->value);
    }

    public function comisiones(): HasMany
    {
        return $this->hasMany(Comision::class);
    }

    public function alumnos(): HasMany
    {
        return $this->hasMany(Alumno::class);
    }

    public function cuotas(): HasMany
    {
        return $this->hasMany(Cuota::class);
    }

    /**
     * P1 Gestión de Carreras: "Buscar por Nombre, Resolución, Modalidad o Estado" (diagrama de estados).
     * "Resolución N° 1234/2020" encuentra la carrera de la resolución 1234/2020.
     */
    public function scopeBuscar(Builder $query, ?string $texto): Builder
    {
        return $query->buscarPalabras(
            $texto,
            ['nombre_carrera', 'resolucion_carrera', 'estado_carrera'],
            ['modalidad' => ['modalidad']],
            ['resolución', 'resolucion', 'n', 'n°', 'nº', 'n.'],
        );
    }

    /** Resolución como se muestra: "Resolución N° 1234/2020" (decisión D1 de E4). */
    protected function resolucionTexto(): Attribute
    {
        return Attribute::get(fn () => ResolucionMinisterial::mostrar($this->resolucion_carrera));
    }

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('estado_carrera', EstadoCarrera::Activo->value);
    }

    /** Combo "Seleccionar Carrera" de Gestión de Plan de Estudio (CU 5–8): "Activo" o "Pendiente" (E6-D2). */
    public function scopeGestionablesEnPlan(Builder $query): Builder
    {
        return $query->whereIn('estado_carrera', [EstadoCarrera::Activo->value, EstadoCarrera::Pendiente->value]);
    }
}
