<?php

namespace Tests\Feature\Alumnos;

use App\Enums\EstadoAlumno;
use App\Enums\EstadoLegajo;
use App\Enums\RolSistema;
use App\Http\Controllers\Panel\Alumnos\AlumnoController;
use App\Mail\InscripcionRegistrada;
use App\Models\Alumno;
use App\Models\Cuota;
use App\Models\DetallePagoCuota;
use App\Models\DocumentoPresentado;
use App\Models\Mes;
use App\Models\Pago;
use App\Models\User;
use App\Notifications\DefinirContrasenia;
use App\Services\Alumnos\InscripcionService;
use Database\Seeders\CatalogosSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

/** CU2 Registrar datos de Inscripción de Alumno (P1 Preinscriptos → P2 → P3). */
class InscripcionTest extends TestCase
{
    use ConRolesYPermisos;

    private Alumno $alumno;

    private Cuota $inscripcion;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CatalogosSeeder::class);
        Storage::fake('local');
        Mail::fake();
        Notification::fake();
        $usuario = User::factory()->sinContrasenia()->create();
        $usuario->assignRole(RolSistema::Alumno->value);
        $this->alumno = Alumno::factory()->create(['user_id' => $usuario->id]);
        $this->inscripcion = Cuota::factory()->create([
            'carrera_id' => $this->alumno->carrera_id, 'mes_id' => Mes::inscripcion()->id, 'anio' => now()->year,
        ]);
        $this->actingAs($this->crearUsuario(RolSistema::AuxiliarAdministrativo));
    }

    /** @return array<string, UploadedFile> */
    private function documentos(): array
    {
        return [
            'documento_titulo' => UploadedFile::fake()->create('titulo.pdf', 100, 'application/pdf'),
            'documento_dni' => UploadedFile::fake()->create('dni.pdf', 100, 'application/pdf'),
        ];
    }

    private function pagarInscripcion(): void
    {
        DetallePagoCuota::factory()->create([
            'cuota_id' => $this->inscripcion->id,
            'pago_id' => Pago::factory()->create(['alumno_id' => $this->alumno->id])->id,
        ]);
    }

    public function test_camino_feliz(): void
    {
        $this->pagarInscripcion();

        $this->post(route('panel.alumnos.datatable'), ['pestania' => 'preinscriptos'])->assertOk()
            ->assertSee(str_replace('/', '\/', route('panel.inscripciones.create', $this->alumno)), false);

        $this->get(route('panel.inscripciones.create', $this->alumno))->assertOk()
            ->assertSee('PASO 2 DE 2')->assertSee('Inscripción '.now()->year)->assertSee('Pagada');

        $this->post(route('panel.inscripciones.store', $this->alumno), $this->documentos())
            ->assertRedirect(route('panel.inscripciones.confirmacion'));

        $this->alumno->refresh();
        $this->assertSame(EstadoAlumno::Inscripto, $this->alumno->estado_alumno);
        $this->assertSame(EstadoLegajo::Activo, $this->alumno->estado_legajo);
        $this->assertSame(config('las.legajo_inicial'), $this->alumno->nro_legajo);
        $this->assertSame(2, DocumentoPresentado::where('user_id', $this->alumno->user_id)->count());
        Mail::assertQueued(InscripcionRegistrada::class, fn ($correo) => $correo->hasTo($this->alumno->usuario->email));
        Notification::assertSentTo($this->alumno->usuario, DefinirContrasenia::class);
        $this->assertStringContainsString('Número de legajo: '.$this->alumno->nro_legajo, (new InscripcionRegistrada($this->alumno))->render());

        $this->get(route('panel.inscripciones.confirmacion'))->assertOk()
            ->assertSee(InscripcionService::CONFIRMACION)->assertSee('NUMERO DE LEGAJO')->assertSee((string) $this->alumno->nro_legajo);
    }

    public function test_inscripcion_no_pagada(): void
    {
        $this->get(route('panel.inscripciones.create', $this->alumno))->assertOk()->assertSee('Pendiente');

        $this->post(route('panel.inscripciones.store', $this->alumno), $this->documentos())
            ->assertRedirect(route('panel.alumnos.index'))
            ->assertSessionHas(AlumnoController::AVISO, InscripcionService::MENSAJE_INSCRIPCION_NO_PAGADA);

        $this->assertSame(EstadoAlumno::Preinscripto, $this->alumno->refresh()->estado_alumno);
        $this->assertNull($this->alumno->nro_legajo);
        $this->assertSame(0, DocumentoPresentado::count());
        Mail::assertNothingQueued();
    }

    public function test_formato_incompatible(): void
    {
        $this->pagarInscripcion();

        $this->from(route('panel.inscripciones.create', $this->alumno))
            ->post(route('panel.inscripciones.store', $this->alumno), [
                ...$this->documentos(),
                'documento_dni' => UploadedFile::fake()->create('dni.docx', 100, 'application/msword'),
            ])
            ->assertRedirect(route('panel.inscripciones.create', $this->alumno))
            ->assertSessionHasErrors(['documento_dni' => InscripcionService::MENSAJE_FORMATO]);

        $this->assertSame(EstadoAlumno::Preinscripto, $this->alumno->refresh()->estado_alumno);
    }
}
