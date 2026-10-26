<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

/**
 * Alta de usuario sin contraseña: link firmado de un solo uso para definirla (plan §5.4).
 */
class DefinirContrasenia extends NotificacionCorreo
{
    public function __construct(public string $url, public int $horas)
    {
        parent::__construct();
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->mensajeBase($notifiable, 'Definir contraseña')
            ->line('Se creó su cuenta de acceso al '.config('app.name').'. Para ingresar, defina su contraseña.')
            ->action('Definir contraseña', $this->url)
            ->line("El enlace se puede usar una sola vez y vence en {$this->horas} horas.")
            ->line('Para ingresar use su correo electrónico: '.$notifiable->email);
    }
}
