<?php

namespace App\Models;

use App\Models\Concerns\EsCatalogo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Catálogo TipoActa (plan §3.3). */
class TipoActa extends Model
{
    use EsCatalogo, HasFactory;

    protected $table = 'tipos_acta';

    protected $primaryKey = 'id';

    protected $fillable = ['descripcion'];

    /** Valor del catálogo que exime de condiciones académicas en CU5 (aclaración p. 72). */
    public const LIBRE = 'Libre';

    public function esLibre(): bool
    {
        return $this->descripcion === self::LIBRE;
    }

    public function actas(): HasMany
    {
        return $this->hasMany(ActaExamen::class);
    }
}
