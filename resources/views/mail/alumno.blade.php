{{-- Layout común de los correos al alumno (App\Mail\CorreoAlumno). --}}
<x-mail::message>
# Hola, {{ $nombre }}:

{{ $introduccion }}

@foreach ($lineas as $linea)
- {{ $linea }}
@endforeach

Saludos,<br>
{{ config('las.instituto_nombre') }}
</x-mail::message>
