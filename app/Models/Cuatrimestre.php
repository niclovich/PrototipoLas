<?php

namespace App\Models;

use App\Models\Concerns\EsCatalogo;
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

    public function detallesPlan(): HasMany
    {
        return $this->hasMany(DetallePlanEstudio::class);
    }
}
