<?php

namespace App\Models;

use App\Models\Concerns\EsCatalogo;
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

    public function novedades(): HasMany
    {
        return $this->hasMany(Novedad::class);
    }
}
