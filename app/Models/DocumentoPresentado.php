<?php

namespace App\Models;

use App\Enums\EstadoGeneral;
use App\Models\Concerns\AuditaCambios;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Clase DocumentoPresentado (Hoja 1): archivo en disco privado. */
class DocumentoPresentado extends Model
{
    use AuditaCambios, HasFactory;

    protected $table = 'documentos_presentados';

    protected $primaryKey = 'id';

    protected $fillable = ['user_id', 'tipo_documento_id', 'nombre_documento', 'fecha_presentacion', 'archivo_documento', 'estado_documento'];

    protected function casts(): array
    {
        return ['fecha_presentacion' => 'date', 'estado_documento' => EstadoGeneral::class];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tipoDocumento(): BelongsTo
    {
        return $this->belongsTo(TipoDocumento::class)->withTrashed();
    }
}
