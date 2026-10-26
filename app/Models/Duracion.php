<?php

namespace App\Models;

use App\Models\Concerns\EsCatalogo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Catálogo Duracion (plan §3.3). */
class Duracion extends Model
{
    use EsCatalogo, HasFactory;

    protected $table = 'duraciones';

    protected $primaryKey = 'id';

    protected $fillable = ['duracion', 'cantidad_anios'];

    protected function casts(): array
    {
        return ['cantidad_anios' => 'integer'];
    }

    public function carreras(): HasMany
    {
        return $this->hasMany(Carrera::class);
    }
}
