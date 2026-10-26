<?php

namespace App\Models;

use App\Models\Concerns\EsCatalogo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Catálogo RolTribunal (plan §3.3). */
class RolTribunal extends Model
{
    use EsCatalogo, HasFactory;

    protected $table = 'roles_tribunal';

    protected $primaryKey = 'id';

    protected $fillable = ['descripcion'];

    public function integrantes(): HasMany
    {
        return $this->hasMany(IntegranteTribunal::class);
    }
}
