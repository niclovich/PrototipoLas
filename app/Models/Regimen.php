<?php

namespace App\Models;

use App\Models\Concerns\EsCatalogo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Catálogo Regimen (plan §3.3). */
class Regimen extends Model
{
    use EsCatalogo, HasFactory;

    protected $table = 'regimenes';

    protected $primaryKey = 'id';

    protected $fillable = ['regimen'];

    public function detallesPlan(): HasMany
    {
        return $this->hasMany(DetallePlanEstudio::class);
    }
}
