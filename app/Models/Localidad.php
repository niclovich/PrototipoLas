<?php

namespace App\Models;

use App\Models\Concerns\EsCatalogo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Catálogo Localidad (plan §3.3): Localidad 1..* — 1..1 Provincia. */
class Localidad extends Model
{
    use EsCatalogo, HasFactory;

    protected $table = 'localidades';

    protected $primaryKey = 'id';

    protected $fillable = ['provincia_id', 'localidad'];

    public function provincia(): BelongsTo
    {
        return $this->belongsTo(Provincia::class)->withTrashed();
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
