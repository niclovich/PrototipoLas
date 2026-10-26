<?php

namespace App\Models;

use App\Models\Concerns\EsCatalogo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Catálogo Provincia (plan §3.3). */
class Provincia extends Model
{
    use EsCatalogo, HasFactory;

    protected $table = 'provincias';

    protected $primaryKey = 'id';

    protected $fillable = ['provincia'];

    public function localidades(): HasMany
    {
        return $this->hasMany(Localidad::class);
    }
}
