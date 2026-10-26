<?php

namespace App\Models;

use App\Models\Concerns\EsCatalogo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Catálogo Modalidad (plan §3.3). */
class Modalidad extends Model
{
    use EsCatalogo, HasFactory;

    protected $table = 'modalidades';

    protected $primaryKey = 'id';

    protected $fillable = ['modalidad'];

    public function carreras(): HasMany
    {
        return $this->hasMany(Carrera::class);
    }

    public function comisiones(): HasMany
    {
        return $this->hasMany(Comision::class);
    }

    public function actas(): HasMany
    {
        return $this->hasMany(ActaExamen::class);
    }
}
