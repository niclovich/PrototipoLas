<?php

namespace Tests\Feature\Seeders;

use App\Enums\EstadoAlumno;
use App\Enums\EstadoCaja;
use App\Enums\EstadoLegajo;
use App\Enums\RolSistema;
use App\Models\ActaExamen;
use App\Models\Alumno;
use App\Models\Beca;
use App\Models\Caja;
use App\Models\Carrera;
use App\Models\Correlativa;
use App\Models\Cuota;
use App\Models\Duracion;
use App\Models\EncuestaSocioeconomica;
use App\Models\Localidad;
use App\Models\MedioPago;
use App\Models\Mes;
use App\Models\Provincia;
use App\Models\TipoGestionTitulo;
use App\Models\User;
use App\Services\Finanzas\DeudaCuotasService;
use Carbon\CarbonImmutable;
use Database\Seeders\CatalogosSeeder;
use Database\Seeders\DemoSeeder;
use Database\Seeders\RolesPermisosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogosDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalogos_idempotente_con_los_valores_del_plan(): void
    {
        $this->seed(CatalogosSeeder::class);
        $this->seed(CatalogosSeeder::class);

        $this->assertSame(24, Provincia::count());
        $this->assertTrue(Localidad::whereHas('provincia', fn ($q) => $q->where('provincia', 'Salta'))->count() > 10);
        $this->assertSame([2, 3, 4, 5], Duracion::orderBy('cantidad_anios')->pluck('cantidad_anios')->all());
        $this->assertSame(
            [1 => 'Inscripción', 2 => 'Marzo', 3 => 'Abril', 4 => 'Mayo', 5 => 'Junio', 6 => 'Julio', 7 => 'Agosto', 8 => 'Septiembre', 9 => 'Octubre', 10 => 'Noviembre', 11 => 'Diciembre'],
            Mes::orderBy('id')->pluck('descripcion', 'id')->all(),
            'Meses precargados en orden: el id es el orden; sin Enero, Febrero ni Seguro.'
        );
        $this->assertEqualsCanonicalizing(['Efectivo', 'Transferencia', 'Tarjeta de Débito'], MedioPago::pluck('medio_pago')->all());
        $this->assertSame(0, TipoGestionTitulo::count(), 'TipoGestionTitulo queda vacío hasta que la tesis defina sus valores.');
    }

    public function test_demo_carga_los_datos_de_prueba_y_el_alumno_puede_ingresar(): void
    {
        $this->travelTo(CarbonImmutable::create(2026, 10, 25, 12));
        $this->seed([RolesPermisosSeeder::class, CatalogosSeeder::class, DemoSeeder::class]);
        $this->seed(DemoSeeder::class); // no duplica
        $deuda = app(DeudaCuotasService::class);

        $this->assertSame(2, Carrera::count(), 'La carrera con plan y una Pendiente sin plan (CU 4).');
        $this->assertSame(12, Carrera::where('nombre_carrera', 'Tecnicatura Superior en Desarrollo de Software')->sole()->planActivo->detalles()->count());
        $this->assertSame(8, Correlativa::count());
        $this->assertSame(11, Cuota::count(), 'Una cuota por cada Mes.');
        $this->assertSame(1, Cuota::deInscripcion()->count());

        $caja = Caja::with('cajero')->sole();
        $this->assertSame(EstadoCaja::Abierta, $caja->estado_caja);
        $this->assertTrue($caja->cajero->tieneRol(RolSistema::Cajero));

        // Alumno al día: Inscripción y mensuales vencidas pagadas, beca Activo vigente hoy.
        $alDia = $this->alumnoDe('alumno@las.test');
        $this->assertSame(EstadoLegajo::Activo, $alDia->estado_legajo);
        $this->assertTrue($deuda->inscripcionPagada($alDia, 2026));
        $this->assertFalse($deuda->tieneCuotasVencidas($alDia));
        $this->assertCount(2, $deuda->cuotasPendientes($alDia), 'Noviembre y Diciembre todavía no vencieron.');
        $this->assertTrue(Beca::activas()->where('alumno_id', $alDia->id)
            ->whereDate('fecha_inicio', '<=', today())->whereDate('fecha_fin', '>=', today())->exists());
        // CU5 / CU6: 5 actas Pendiente (una Libre) y alumno@ inscripto a la 0001.
        $this->assertSame(5, ActaExamen::count());
        $this->assertTrue($alDia->detallesActa()->whereHas('acta', fn ($q) => $q->where('nro_acta', 1))->exists());

        // Moroso: Inscripción pagada y Marzo a Octubre vencidas impagas.
        $moroso = $this->alumnoDe('moroso@las.test');
        $this->assertTrue($deuda->inscripcionPagada($moroso, 2026));
        $this->assertSame(['Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre'], $deuda->cuotasVencidas($moroso)->pluck('mes.descripcion')->all());

        // Preinscripto: solo la Inscripción pendiente.
        // CU 9: cada usuario Alumno demo tiene su Encuesta; la segunda ejecución no duplica.
        $this->assertSame(User::role(RolSistema::Alumno->value)->count(), EncuestaSocioeconomica::count());
        $this->assertSame(3, EncuestaSocioeconomica::count());

        $preinscripto = $this->alumnoDe('preinscripto@las.test');
        $this->assertSame(EstadoAlumno::Preinscripto, $preinscripto->estado_alumno);
        $this->assertFalse($deuda->inscripcionPagada($preinscripto, 2026));
        $this->assertSame([Mes::INSCRIPCION], $deuda->cuotasPendientes($preinscripto)->pluck('mes.descripcion')->all());

        $this->post(route('login'), ['email' => 'alumno@las.test', 'password' => DemoSeeder::CONTRASENIA])->assertRedirect('/panel');
        $this->assertAuthenticated();
    }

    private function alumnoDe(string $email): Alumno
    {
        return Alumno::whereHas('usuario', fn ($q) => $q->where('email', $email))->sole();
    }
}
