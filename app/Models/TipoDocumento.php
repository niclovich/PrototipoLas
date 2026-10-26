<?php

namespace App\Models;

use App\Models\Concerns\EsCatalogo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Catálogo TipoDocumento (plan §3.3). */
class TipoDocumento extends Model
{
    use EsCatalogo, HasFactory;

    protected $table = 'tipos_documento';

    protected $primaryKey = 'id';

    protected $fillable = ['descripcion'];

    public function documentos(): HasMany
    {
        return $this->hasMany(DocumentoPresentado::class);
    }
}
