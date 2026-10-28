<?php

namespace App\Support;

/** Etiquetas en español de los eventos de spatie/laravel-activitylog. */
final class EventoAuditoria
{
    public const EVENTOS = ['created' => 'Alta', 'updated' => 'Modificación', 'deleted' => 'Baja', 'restored' => 'Reactivación'];

    public static function etiqueta(?string $evento): string
    {
        return self::EVENTOS[$evento] ?? (string) $evento;
    }
}
