<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

class RestablecerContrasenia extends NotificacionCorreo
{
    public function __construct(public string $token)
    {
        parent::__construct();
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('password.reset', ['token' => $this->token, 'email' => $notifiable->getEmailForPasswordReset()]);
        $minutos = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return $this->mensajeBase($notifiable, 'Restablecer contraseña')
            ->line('Recibimos una solicitud para restablecer la contraseña de su cuenta.')
            ->action('Restablecer contraseña', $url)
            ->line("Este enlace vence en {$minutos} minutos.")
            ->line('Si usted no lo solicitó, ignore este correo.');
    }
}
