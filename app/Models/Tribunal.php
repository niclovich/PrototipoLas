<?php

namespace App\Models;

use App\Models\Concerns\AuditaCambios;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/** Clase Tribunal (Hoja 3): sin atributos, 1..1 con ActaExamen y 1..* IntegranteTribunal. */
class Tribunal extends Model
{
    use AuditaCambios, HasFactory;

    protected $table = 'tribunales';

    protected $primaryKey = 'id';

    protected $fillable = [];

    public function acta(): HasOne
    {
        return $this->hasOne(ActaExamen::class);
    }

    public function integrantes(): HasMany
    {
        return $this->hasMany(IntegranteTribunal::class);
    }
}
