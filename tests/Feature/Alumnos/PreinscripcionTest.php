<?php

namespace Tests\Feature\Alumnos;

use App\Enums\EstadoAlumno;
use App\Enums\RolSistema;
use App\Enums\SituacionEstudios;
use App\Http\Controllers\Panel\Alumnos\PreinscripcionController;
use App\Models\Alumno;
use App\Models\Carrera;
use App\Models\DocumentoPresentado;
use App\Models\Localidad;
use App\Models\PlanEstudio;
use App\Models\TipoDocumento;
use App\Models\User;
use App\Services\Alumnos\PreinscripcionService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

/** CU1 Registrar datos de Preinscripción de Alumno (asistente P1–P5 con P2/P3 del CU 9). */
class PreinscripcionTest extends TestCase
{
    use ConRolesYPermisos;

    private Carrera $carrera;

    private Localidad $localidad;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');
        TipoDocumento::create(['descripcion' => TipoDocumento::FOTOCOPIA_DNI]);
        TipoDocumento::create(['descripcion' => TipoDocumento::CERTIFICADO_TITULO]);
        $this->carrera = PlanEstudio::factory()->create()->carrera;
        $this->localidad = Localidad::factory()->create();
        $this->actingAs($this->crearUsuario(RolSistema::Preceptor));
    }

    private function p4(array $cambios = []): array
    {
        return [
            'carrera_id' => $this->carrera->id,
            'situacion_estudios' => SituacionEstudios::EnCurso->value,
            'adeuda_materia' => '1',
            ...$cambios,
        ];
    }

    public function test_camino_feliz_alumno_nuevo(): void
    {
        $this->post(route('panel.preinscripcion.buscar'), ['dni' => '41.222.333'])
            ->assertRedirect(route('panel.preinscripcion.datos-personales'))
            ->assertSessionHas(PreinscripcionController::AVISO_DNI_INEXISTENTE);

        $this->get(route('panel.preinscripcion.datos-personales'))->assertOk()
            ->assertSee(PreinscripcionService::MENSAJE_DNI_INEXISTENTE)->assertSee('41222333');

        $this->post(route('panel.preinscripcion.datos-personales.store'), [
            'apellido_usuario' => 'Muñoz', 'nombre_usuario' => 'Nicolás Matías', 'email' => 'nicolasm@correo.com',
            'fecha_nacimiento' => '13/11/1998', 'provincia_id' => $this->localidad->provincia_id, 'localidad_id' => $this->localidad->id,
            'foto' => UploadedFile::fake()->image('foto.jpg'),
        ])->assertRedirect(route('panel.preinscripcion.encuesta'));

        $this->post(route('panel.preinscripcion.encuesta.store'), ['padres_viven' => '1', 'recibe_ayuda_economica' => '1'])
            ->assertRedirect(route('panel.preinscripcion.carrera'));
        $this->assertDatabaseMissing('users', ['dni_usuario' => '41222333']);

        $this->post(route('panel.preinscripcion.carrera.store'), $this->p4([
            'documento_dni' => UploadedFile::fake()->create('dni.pdf', 100, 'application/pdf'),
            'documento_titulo' => UploadedFile::fake()->create('titulo.pdf', 100, 'application/pdf'),
            'observaciones' => 'Presenta el título en marzo.',
        ]))->assertRedirect(route('panel.preinscripcion.confirmar'))->assertSessionHas(PreinscripcionController::AVISO_DOCUMENTACION, false);

        $this->get(route('panel.preinscripcion.confirmar'))->assertOk()
            ->assertSeeInOrder(['Constancia de Titulo', 'Si', 'Observaciones', 'Presenta el título en marzo.'])
            ->assertSee('Nuevo alumno')->assertSee('Ambos padres viven')->assertSee($this->carrera->nombre_carrera)
            ->assertDontSee('data-abrir', false);

        $this->post(route('panel.preinscripcion.store'))
            ->assertRedirect(route('panel.preinscripcion.create'))
            ->assertSessionHas('success', PreinscripcionService::MENSAJE_REGISTRO_EXITOSO);

        $usuario = User::where('dni_usuario', '41222333')->firstOrFail();
        $this->assertTrue($usuario->tieneRol(RolSistema::Alumno));
        $this->assertTrue($usuario->encuesta->padres_viven);
        Storage::disk('public')->assertExists($usuario->foto_usuario);
        $alumno = Alumno::where('user_id', $usuario->id)->firstOrFail();
        $this->assertSame(EstadoAlumno::Preinscripto, $alumno->estado_alumno);
        $this->assertSame($this->carrera->planActivo->id, $alumno->plan_estudio_id);
        $this->assertTrue($alumno->adeuda_materia);
        $this->assertSame('Presenta el título en marzo.', $alumno->observaciones);
        $this->assertNull($alumno->nro_legajo);
        $this->assertSame(2, DocumentoPresentado::where('user_id', $usuario->id)->count());
        DocumentoPresentado::all()->each(fn ($d) => Storage::disk('local')->assertExists($d->archivo_documento));
        $this->assertSame([], Storage::disk('local')->files(PreinscripcionService::DIRECTORIO_TEMPORAL));
    }

    public function test_camino_feliz_alumno_existente_va_directo_a_p4(): void
    {
        $existente = $this->crearUsuario(RolSistema::Alumno, ['dni_usuario' => '30123456']);

        $this->post(route('panel.preinscripcion.buscar'), ['dni' => '30123456'])->assertRedirect(route('panel.preinscripcion.carrera'));
        $this->get(route('panel.preinscripcion.datos-personales'))->assertRedirect(route('panel.preinscripcion.create'));
        $this->get(route('panel.preinscripcion.carrera'))->assertOk()->assertSee(route('panel.preinscripcion.create'));

        $this->post(route('panel.preinscripcion.carrera.store'), $this->p4())->assertRedirect(route('panel.preinscripcion.confirmar'));
        $this->get(route('panel.preinscripcion.confirmar'))->assertOk()->assertSee('Alumno registrado');
        $this->post(route('panel.preinscripcion.store'))->assertSessionHas('success');

        $this->assertDatabaseHas('alumnos', ['user_id' => $existente->id, 'carrera_id' => $this->carrera->id, 'estado_alumno' => 'Preinscripto']);
    }

    public function test_dni_del_personal_se_rechaza(): void
    {
        $this->crearUsuario(RolSistema::Docente, ['dni_usuario' => '20555555']);

        $this->from(route('panel.preinscripcion.create'))
            ->post(route('panel.preinscripcion.buscar'), ['dni' => '20555555'])
            ->assertRedirect(route('panel.preinscripcion.create'))
            ->assertSessionHasErrors(['dni' => PreinscripcionService::MENSAJE_DNI_PERSONAL]);
    }

    public function test_documentacion_no_adjuntada(): void
    {
        $this->crearUsuario(RolSistema::Alumno, ['dni_usuario' => '30123456']);
        $this->post(route('panel.preinscripcion.buscar'), ['dni' => '30123456']);

        $this->post(route('panel.preinscripcion.carrera.store'), $this->p4([
            'documento_dni' => UploadedFile::fake()->create('dni.pdf', 100, 'application/pdf'),
        ]))->assertSessionHas(PreinscripcionController::AVISO_DOCUMENTACION, true);

        $this->get(route('panel.preinscripcion.confirmar'))->assertSee(PreinscripcionService::MENSAJE_DOCUMENTACION_NO_ADJUNTADA)->assertSee('data-abrir', false);
        $this->post(route('panel.preinscripcion.store'))->assertSessionHas('success');
        $this->assertSame(1, DocumentoPresentado::count());
    }

    public function test_alumno_ya_registrado_en_la_carrera(): void
    {
        $existente = $this->crearUsuario(RolSistema::Alumno, ['dni_usuario' => '30123456']);
        Alumno::factory()->create(['user_id' => $existente->id, 'carrera_id' => $this->carrera->id, 'plan_estudio_id' => $this->carrera->planActivo->id]);
        $this->post(route('panel.preinscripcion.buscar'), ['dni' => '30123456']);
        $this->post(route('panel.preinscripcion.carrera.store'), $this->p4());

        $this->post(route('panel.preinscripcion.store'))
            ->assertRedirect(route('panel.preinscripcion.carrera'))
            ->assertSessionHasErrors(['carrera_id' => PreinscripcionService::MENSAJE_YA_EN_CARRERA]);
    }

    public function test_solo_carreras_activas_con_plan_y_documento_pdf(): void
    {
        $sinPlan = Carrera::factory()->create();
        $this->crearUsuario(RolSistema::Alumno, ['dni_usuario' => '30123456']);
        $this->post(route('panel.preinscripcion.buscar'), ['dni' => '30123456']);

        $this->post(route('panel.preinscripcion.carrera.store'), $this->p4([
            'carrera_id' => $sinPlan->id,
            'documento_dni' => UploadedFile::fake()->create('dni.docx', 10, 'application/msword'),
        ]))->assertSessionHasErrors(['carrera_id', 'documento_dni' => 'El archivo debe estar en formato PDF.']);
    }

    public function test_nueva_busqueda_descarta_los_temporales(): void
    {
        $this->post(route('panel.preinscripcion.buscar'), ['dni' => '41222333']);
        $this->post(route('panel.preinscripcion.datos-personales.store'), [
            'apellido_usuario' => 'Muñoz', 'nombre_usuario' => 'Ana', 'email' => 'ana@correo.com',
            'provincia_id' => $this->localidad->provincia_id, 'localidad_id' => $this->localidad->id,
            'foto' => UploadedFile::fake()->image('foto.jpg'),
        ]);
        $this->assertCount(1, Storage::disk('public')->allFiles());

        $this->post(route('panel.preinscripcion.buscar'), ['dni' => '41222334']);
        $this->assertCount(0, Storage::disk('public')->allFiles());
    }
}
