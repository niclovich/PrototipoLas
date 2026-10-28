<?php

namespace App\Models;

use App\Models\Concerns\EsCatalogo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Catálogo Motivo (plan §3.3). */
class Motivo extends Model
{
    use EsCatalogo, HasFactory;

    protected $table = 'motivos';

    protected $primaryKey = 'id';

    protected $fillable = ['descripcion', 'accion'];

    public const ACCION_BAJA_PERSONAL = 'Baja de Personal';

    /** Acciones que filtran los CU (CU 27). Se agregan acá cuando un CU use otra. */
    public const ACCIONES = [self::ACCION_BAJA_PERSONAL];

    public function scopeDeAccion(Builder $query, string $accion): Builder
    {
        return $query->where('accion', $accion);
    }

    public function novedades(): HasMany
    {
        return $this->hasMany(Novedad::class);
    }
}
