<?php

namespace App\Models;

use App\Models\Concerns\EsCatalogo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Catálogo Cuatrimestre (plan §3.3). */
class Cuatrimestre extends Model
{
    use EsCatalogo, HasFactory;

    protected $table = 'cuatrimestres';

    protected $primaryKey = 'id';

    protected $fillable = ['cuatrimestre'];

    /** Texto de la tabla de P1 y de la Confirmación de CU 6: "1er Cuatrimestre" → "1C" (E6-D6). */
    protected function abreviatura(): Attribute
    {
        return Attribute::get(fn () => preg_match('/^(\d+)\D*\s+Cuatrimestre$/u', (string) $this->cuatrimestre, $m)
            ? "{$m[1]}C"
            : (string) $this->cuatrimestre);
    }

    public function detallesPlan(): HasMany
    {
        return $this->hasMany(DetallePlanEstudio::class);
    }
}
