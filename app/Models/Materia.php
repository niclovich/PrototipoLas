<?php

namespace App\Models;

use App\Enums\EstadoGeneral;
use App\Models\Concerns\AuditaCambios;
use App\Models\Concerns\BuscaPorPalabras;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Clase Materia (Hoja 2). */
class Materia extends Model
{
    use AuditaCambios, BuscaPorPalabras, HasFactory;

    protected $table = 'materias';

    protected $primaryKey = 'id';

    protected $fillable = ['nombre_materia', 'estado_materia'];

    protected function casts(): array
    {
        return ['estado_materia' => EstadoGeneral::class];
    }

    public function detallesPlan(): HasMany
    {
        return $this->hasMany(DetallePlanEstudio::class);
    }

    /** P1 Gestión de Materias: "Buscar por nombre". */
    public function scopeBuscar(Builder $query, ?string $texto): Builder
    {
        return $query->buscarPalabras($texto, ['nombre_materia']);
    }

    /**
     * CU 2 alt. 4 (decisión D3 de E5): materias con un DetallePlanEstudio "Activo" en un Plan de Estudio "Activo".
     * Una materia dada de baja del plan (CU 7) o de un plan inactivo no cuenta.
     */
    public function scopeVinculadasAPlanActivo(Builder $query): Builder
    {
        return $query->whereHas('detallesPlan', fn (Builder $detalle) => $detalle->activos()
            ->whereHas('planEstudio', fn (Builder $plan) => $plan->activos()));
    }

    public function vinculadaAPlanActivo(): bool
    {
        return static::query()->whereKey($this->getKey())->vinculadasAPlanActivo()->exists();
    }

    /** Materias "Activo" (las que se pueden agregar a un plan, E7). */
    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('estado_materia', EstadoGeneral::Activo->value);
    }
}
