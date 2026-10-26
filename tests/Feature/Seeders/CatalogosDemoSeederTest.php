<?php

namespace Tests\Feature\Seeders;

use App\Enums\EstadoCaja;
use App\Enums\EstadoLegajo;
use App\Models\Alumno;
use App\Models\Caja;
use App\Models\Carrera;
use App\Models\Correlativa;
use App\Models\Cuota;
use App\Models\Duracion;
use App\Models\Localidad;
use App\Models\MedioPago;
use App\Models\Mes;
use App\Models\Provincia;
use App\Models\TipoGestionTitulo;
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
        $this->assertSame([2, 3, 4], Duracion::orderBy('cantidad_anios')->pluck('cantidad_anios')->all());
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
        $this->seed([RolesPermisosSeeder::class, CatalogosSeeder::class, DemoSeeder::class]);
        $this->seed(DemoSeeder::class); // no duplica

        $this->assertSame(1, Carrera::count());
        $this->assertSame(12, Carrera::first()->planActivo->detalles()->count());
        $this->assertSame(8, Correlativa::count());
        $this->assertSame(11, Cuota::count(), 'Una cuota por cada Mes.');
        $this->assertSame(1, Cuota::deInscripcion()->count());
        $this->assertSame(EstadoCaja::Abierta, Caja::first()->estado_caja);
        $this->assertSame(1, Alumno::where('estado_legajo', EstadoLegajo::Activo->value)->count());

        $this->post(route('login'), ['email' => 'alumno@las.test', 'password' => DemoSeeder::CONTRASENIA])->assertRedirect('/panel');
        $this->assertAuthenticated();
    }
}
