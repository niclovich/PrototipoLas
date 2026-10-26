<?php

namespace App\Models;

use App\Enums\EstadoGeneral;
use App\Models\Concerns\AuditaCambios;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Clase IntegranteTribunal (Hoja 3): Docente con un RolTribunal; la baja es por estado "Inactivo". */
class IntegranteTribunal extends Model
{
    use AuditaCambios, HasFactory;

    protected $table = 'integrantes_tribunal';

    protected $primaryKey = 'id';

    protected $fillable = ['tribunal_id', 'user_id', 'rol_tribunal_id', 'estado_integrante'];

    protected function casts(): array
    {
        return ['estado_integrante' => EstadoGeneral::class];
    }

    public function tribunal(): BelongsTo
    {
        return $this->belongsTo(Tribunal::class);
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function rolTribunal(): BelongsTo
    {
        return $this->belongsTo(RolTribunal::class)->withTrashed();
    }
}
