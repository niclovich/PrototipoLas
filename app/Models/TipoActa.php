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

    public function actas(): HasMany
    {
        return $this->hasMany(ActaExamen::class);
    }
}
