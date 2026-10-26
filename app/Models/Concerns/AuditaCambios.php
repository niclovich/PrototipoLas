<?php

namespace App\Models\Concerns;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Auditoría común de los modelos (spatie/laravel-activitylog): todos los atributos
 * que cambiaron (también los asignados con forceFill), sin campos sensibles ni timestamps. El causer es el usuario autenticado.
 */
trait AuditaCambios
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logExcept(['password', 'remember_token', 'created_at', 'updated_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName($this->getTable());
    }
}
