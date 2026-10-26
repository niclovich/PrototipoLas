<?php

namespace Database\Seeders;

use App\Models\Cuatrimestre;
use App\Models\Duracion;
use App\Models\Llamado;
use App\Models\Localidad;
use App\Models\MedioPago;
use App\Models\Mes;
use App\Models\Modalidad;
use App\Models\Motivo;
use App\Models\Provincia;
use App\Models\Regimen;
use App\Models\RolTribunal;
use App\Models\TipoActa;
use App\Models\TipoDocumento;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

/**
 * Valores iniciales de los catálogos (plan §5.7, aceptados por Nicolás). Idempotente: updateOrCreate
 * por clave natural, incluso sobre registros dados de baja (withTrashed).
 * TipoGestionTitulo queda vacío: la tesis no define sus valores (pendiente, CU fuera de alcance).
 */
class CatalogosSeeder extends Seeder
{
    /** Capital de cada provincia (24 jurisdicciones). */
    private const PROVINCIAS = [
        'Buenos Aires' => ['La Plata'],
        'Ciudad Autónoma de Buenos Aires' => ['Ciudad Autónoma de Buenos Aires'],
        'Catamarca' => ['San Fernando del Valle de Catamarca'],
        'Chaco' => ['Resistencia'],
        'Chubut' => ['Rawson'],
        'Córdoba' => ['Córdoba'],
        'Corrientes' => ['Corrientes'],
        'Entre Ríos' => ['Paraná'],
        'Formosa' => ['Formosa'],
        'Jujuy' => ['San Salvador de Jujuy'],
        'La Pampa' => ['Santa Rosa'],
        'La Rioja' => ['La Rioja'],
        'Mendoza' => ['Mendoza'],
        'Misiones' => ['Posadas'],
        'Neuquén' => ['Neuquén'],
        'Río Negro' => ['Viedma'],
        'Salta' => [
            'Salta', 'San Ramón de la Nueva Orán', 'Tartagal', 'General Güemes', 'San José de Metán',
            'Rosario de la Frontera', 'Cafayate', 'Cerrillos', 'Rosario de Lerma', 'Embarcación',
            'Joaquín V. González', 'Vaqueros', 'La Caldera', 'Campo Quijano', 'Chicoana', 'El Carril',
            'Pichanal', 'Colonia Santa Rosa', 'Hipólito Yrigoyen', 'Aguaray', 'Apolinario Saravia',
        ],
        'San Juan' => ['San Juan'],
        'San Luis' => ['San Luis'],
        'Santa Cruz' => ['Río Gallegos'],
        'Santa Fe' => ['Santa Fe'],
        'Santiago del Estero' => ['Santiago del Estero'],
        'Tierra del Fuego, Antártida e Islas del Atlántico Sur' => ['Ushuaia'],
        'Tucumán' => ['San Miguel de Tucumán'],
    ];

    public function run(): void
    {
        foreach (self::PROVINCIAS as $nombre => $localidades) {
            $provincia = Provincia::withTrashed()->updateOrCreate(['provincia' => $nombre]);
            foreach ($localidades as $localidad) {
                Localidad::withTrashed()->updateOrCreate(['provincia_id' => $provincia->id, 'localidad' => $localidad]);
            }
        }

        $this->simples(Modalidad::class, 'modalidad', ['Presencial', 'Semipresencial', 'A distancia']);
        $this->simples(Regimen::class, 'regimen', ['Anual', 'Cuatrimestral']);
        $this->simples(Cuatrimestre::class, 'cuatrimestre', ['1er Cuatrimestre', '2do Cuatrimestre', 'Anual']);
        $this->simples(Llamado::class, 'llamado', ['P · 1° Llamado', 'S · 2° Llamado', 'E · Extraordinario']);
        $this->simples(TipoActa::class, 'descripcion', ['Regular', 'Libre']);
        $this->simples(RolTribunal::class, 'descripcion', ['Presidente', 'Vocal 1', 'Vocal 2']);
        $this->simples(TipoDocumento::class, 'descripcion', ['Fotocopia de DNI', 'Certificado de Título']);
        $this->simples(MedioPago::class, 'medio_pago', ['Efectivo', 'Transferencia', 'Tarjeta de Débito']);

        foreach (array_keys(Mes::VALORES) as $orden => $mes) {
            Mes::updateOrCreate(['id' => $orden + 1], ['descripcion' => $mes]);
        }

        foreach ([2, 3, 4] as $anios) {
            Duracion::withTrashed()->updateOrCreate(['duracion' => "{$anios} años"], ['cantidad_anios' => $anios]);
        }

        foreach (['Renuncia', 'Jubilación', 'Fin de contrato', 'Otro'] as $motivo) {
            Motivo::withTrashed()->updateOrCreate(['descripcion' => $motivo, 'accion' => Motivo::ACCION_BAJA_PERSONAL]);
        }
    }

    /**
     * @param  class-string<Model>  $modelo
     * @param  list<string>  $valores
     */
    private function simples(string $modelo, string $columna, array $valores): void
    {
        foreach ($valores as $valor) {
            $modelo::withTrashed()->updateOrCreate([$columna => $valor]);
        }
    }
}
