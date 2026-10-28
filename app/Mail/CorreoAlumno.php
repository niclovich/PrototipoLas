<?php

namespace App\Mail;

use App\Models\Alumno;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Correo al alumno de los CU principales (skill §4): layout común `mail.alumno`, encolado y después del commit
 * (un fallo del correo no revierte el CU). Los datos se copian como texto: la cola no serializa modelos.
 */
abstract class CorreoAlumno extends Mailable implements ShouldQueue
{
    use Queueable;

    public string $nombre;

    /** @var list<string> */
    public array $lineas;

    public function __construct(Alumno $alumno)
    {
        $alumno->loadMissing(['usuario', 'carrera']);
        $this->nombre = (string) $alumno->usuario->nombre_usuario;
        $this->lineas = $this->detalle($alumno);
        $this->afterCommit();
    }

    abstract protected function asunto(): string;

    abstract protected function introduccion(): string;

    /** @return list<string> */
    abstract protected function detalle(Alumno $alumno): array;

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->asunto().' - '.config('app.name'));
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.alumno', with: ['introduccion' => $this->introduccion()]);
    }
}
