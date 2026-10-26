<?php

namespace App\Models;

use App\Models\Concerns\EsCatalogo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Catálogo Llamado (plan §3.3). */
class Llamado extends Model
{
    use EsCatalogo, HasFactory;

    protected $table = 'llamados';

    protected $primaryKey = 'id';

    protected $fillable = ['llamado'];

    public function actas(): HasMany
    {
        return $this->hasMany(ActaExamen::class);
    }
}
