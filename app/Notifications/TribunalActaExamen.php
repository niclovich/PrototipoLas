<?php

namespace App\Notifications;

use App\Models\ActaExamen;
use Illuminate\Notifications\Messages\MailMessage;

/** CU 21 paso 32 y CU 22 paso 33: aviso a los Docentes del tribunal (o desvinculados) de un Acta de Examen. */
class TribunalActaExamen extends NotificacionCorreo
{
    public const INTEGRA = 'integra';

    public const DESVINCULADO = 'desvinculado';

    public function __construct(private readonly ActaExamen $acta, private readonly string $situacion = self::INTEGRA)
    {
        parent::__construct();
    }

    public function toMail(object $notifiable): MailMessage
    {
        $acta = $this->acta->loadMissing(['detallePlan.materia', 'llamado']);
        $linea = "Acta N° {$acta->nro_acta_texto} · {$acta->detallePlan->materia->nombre_materia} · {$acta->llamado->llamado} · {$acta->fecha_acta->format('d/m/Y')}";

        return $this->mensajeBase($notifiable, 'Tribunal de Acta de Examen')
            ->line($this->situacion === self::DESVINCULADO
                ? 'Fue desvinculado del tribunal del siguiente Acta de Examen:'
                : 'Integra el tribunal del siguiente Acta de Examen:')
            ->line($linea);
    }
}
