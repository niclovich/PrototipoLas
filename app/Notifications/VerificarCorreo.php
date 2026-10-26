<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;

class VerificarCorreo extends NotificacionCorreo
{
    public function toMail(object $notifiable): MailMessage
    {
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(config('auth.verification.expire', 60)),
            ['id' => $notifiable->getKey(), 'hash' => sha1($notifiable->getEmailForVerification())],
        );

        return $this->mensajeBase($notifiable, 'Verificar correo electrónico')
            ->line('Haga clic en el botón para confirmar su dirección de correo electrónico.')
            ->action('Verificar correo electrónico', $url)
            ->line('Si usted no creó una cuenta, no es necesario hacer nada.');
    }
}
