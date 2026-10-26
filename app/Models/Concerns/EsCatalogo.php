<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Catálogos sin CU propio (plan §3.3): auditoría + SoftDeletes para el ABM genérico del SUPERADMIN.
 */
trait EsCatalogo
{
    use AuditaCambios, SoftDeletes;
}
