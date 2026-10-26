<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Base de las notificaciones por correo: encoladas, después del commit y con el mismo saludo.
 */
abstract class NotificacionCorreo extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        $this->afterCommit();
    }

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    protected function mensajeBase(object $notifiable, string $asunto): MailMessage
    {
        return (new MailMessage)
            ->subject($asunto.' - '.config('app.name'))
            ->greeting('Hola, '.$notifiable->nombre_usuario.':')
            ->salutation('Saludos, '.config('las.instituto_nombre'));
    }
}
