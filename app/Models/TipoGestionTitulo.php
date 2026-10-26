<?php

namespace App\Models;

use App\Models\Concerns\EsCatalogo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Catálogo TipoGestionTitulo (plan §3.3). */
class TipoGestionTitulo extends Model
{
    use EsCatalogo, HasFactory;

    protected $table = 'tipos_gestion_titulo';

    protected $primaryKey = 'id';

    protected $fillable = ['descripcion'];

    public function titulos(): HasMany
    {
        return $this->hasMany(TituloTramite::class);
    }
}
