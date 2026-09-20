<?php

namespace Tests\Feature;

use App\Models\Bien;
use App\Models\Licencia;
use App\Models\LicenciaAsignacion;
use App\Models\LicenciaDocumento;
use App\Models\LicenciaHistorial;
use App\Models\LicenciaRenovacion;
use App\Models\Proveedor;
use App\Models\Responsable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LicenciasBaseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_license_module_tables_have_the_expected_structure(): void
    {
        $this->assertTrue(Schema::hasColumns('proveedores', [
            'id', 'nombre', 'razon_social', 'rfc', 'contacto_nombre', 'contacto_email',
            'contacto_telefono', 'sitio_web', 'activo', 'observaciones', 'created_at', 'updated_at',
        ]));
        $this->assertTrue(Schema::hasColumns('licencias', [
            'id', 'clave', 'nombre', 'descripcion', 'fabricante', 'producto', 'version',
            'tipo_licencia', 'modalidad', 'cantidad_adquirida', 'numero_licencia',
            'clave_producto', 'numero_contrato', 'fecha_adquisicion', 'fecha_inicio',
            'fecha_vencimiento', 'renovacion_automatica', 'costo_unitario', 'costo_total',
            'moneda', 'estado', 'dependencia_id_accesos', 'area_id_accesos',
            'responsable_id', 'proveedor_id', 'observaciones', 'created_at', 'updated_at',
        ]));
        $this->assertTrue(Schema::hasColumns('licencia_asignaciones', [
            'licencia_id', 'tipo_asignacion', 'responsable_id', 'activo_id',
            'usuario_id_accesos', 'dependencia_id_accesos', 'area_id_accesos',
            'fecha_asignacion', 'fecha_retiro', 'estado', 'observaciones',
        ]));
        $this->assertTrue(Schema::hasColumns('licencia_sistema', [
            'licencia_id', 'sistema_id', 'cantidad', 'observaciones',
        ]));
        $this->assertTrue(Schema::hasColumns('licencia_renovaciones', [
            'licencia_id', 'fecha_renovacion', 'vigencia_desde', 'vigencia_hasta',
            'costo', 'moneda', 'numero_contrato', 'numero_factura', 'proveedor_id', 'observaciones',
        ]));
        $this->assertTrue(Schema::hasColumns('licencia_documentos', [
            'licencia_id', 'tipo', 'nombre', 'archivo', 'descripcion',
        ]));
        $this->assertTrue(Schema::hasColumns('licencia_historial', [
            'licencia_id', 'accion', 'campo', 'valor_anterior', 'valor_nuevo',
            'usuario_id_accesos', 'usuario_nombre', 'descripcion',
        ]));
        $this->assertFalse(Schema::hasColumn('licencias', 'cantidad_disponible'));
    }

    public function test_license_models_use_the_expected_tables_and_relationships(): void
    {
        $licencia = new Licencia;

        $this->assertSame('proveedores', (new Proveedor)->getTable());
        $this->assertSame('licencia_asignaciones', (new LicenciaAsignacion)->getTable());
        $this->assertSame('licencia_renovaciones', (new LicenciaRenovacion)->getTable());
        $this->assertSame('licencia_documentos', (new LicenciaDocumento)->getTable());
        $this->assertSame('licencia_historial', (new LicenciaHistorial)->getTable());
        $this->assertSame('bienes', (new LicenciaAsignacion)->activo()->getRelated()->getTable());
        $this->assertSame('licencia_sistema', $licencia->sistemas()->getTable());
        $this->assertSame('cantidad', $licencia->sistemas()->getPivotColumns()[0]);
        $this->assertInstanceOf(Bien::class, (new LicenciaAsignacion)->activo()->getRelated());
    }

    public function test_license_and_provider_placeholder_routes_are_available_in_the_sidebar(): void
    {
        Responsable::create([
            'nombre' => 'Ana',
            'apellido_paterno' => 'López',
            'dependencia_id_accesos' => 'Tecnologías de la Información',
            'activo' => true,
        ]);

        $this->get(route('licencia.index'))
            ->assertOk()
            ->assertSee('Inventario de licencias de software institucionales.')
            ->assertSee('href="'.route('licencia.create').'"', false)
            ->assertSee('data-dropdown-toggle="licencias-menu"', false)
            ->assertSee('id="licencias-menu" class="dropdown-panel is-open"', false)
            ->assertSee('href="'.route('licencia.resumen').'"', false)
            ->assertSee('href="'.route('licencia.index').'"', false)
            ->assertSee('href="'.route('licencia.proveedores').'"', false);

        $this->get(route('licencia.proveedores'))
            ->assertOk()
            ->assertSee('Catálogo de proveedores de licencias y servicios de software.')
            ->assertSee('Directorio de proveedores')
            ->assertSee('data-nuevo-proveedor', false);

        $this->get(route('licencia.create'))
            ->assertOk()
            ->assertSee('Registrar licencia')
            ->assertSee('value="LIC-0001"', false)
            ->assertSee('disabled aria-describedby="clave-ayuda"', false)
            ->assertDontSee('name="clave"', false)
            ->assertSee('data-nuevo-proveedor', false)
            ->assertSee('modal.showModal()', false)
            ->assertSee('list="licencia-responsables"', false)
            ->assertSee('type="hidden" name="responsable_id"', false)
            ->assertSee('Ana López')
            ->assertSee('name="tipo_licencia"', false)
            ->assertSee('name="cantidad_adquirida"', false)
            ->assertSee('name="clave_producto"', false)
            ->assertSee('name="renovacion_automatica"', false)
            ->assertSee('action="'.route('licencia.store').'"', false)
            ->assertSee('Guardar licencia');
    }

    public function test_license_summary_and_inventory_use_live_data(): void
    {
        $proveedor = Proveedor::create(['nombre' => 'Proveedor institucional']);
        Licencia::create([
            'clave' => 'LIC-001',
            'nombre' => 'Suite institucional',
            'producto' => 'Suite Office',
            'tipo_licencia' => 'suscripcion',
            'modalidad' => 'usuario',
            'cantidad_adquirida' => 25,
            'estado' => 'activa',
            'proveedor_id' => $proveedor->id,
            'fecha_vencimiento' => today()->addMonths(6),
        ]);
        Licencia::create([
            'clave' => 'LIC-002',
            'nombre' => 'Protección de equipos',
            'producto' => 'Antivirus',
            'tipo_licencia' => 'volumen',
            'cantidad_adquirida' => 50,
            'estado' => 'por_vencer',
            'fecha_vencimiento' => today()->addDays(20),
        ]);

        $this->get(route('licencia.resumen'))
            ->assertOk()
            ->assertSee('Panorama general del licenciamiento institucional')
            ->assertSee('Suite institucional')
            ->assertSee('Protección de equipos')
            ->assertViewHas('metricas', fn (array $metricas) => $metricas === [
                'total' => 2,
                'activas' => 1,
                'por_vencer' => 1,
                'vencidas' => 0,
                'cantidad_adquirida' => 75,
            ]);

        $this->get(route('licencia.index', ['estado' => 'por_vencer']))
            ->assertOk()
            ->assertSee('Protección de equipos')
            ->assertDontSee('Suite institucional')
            ->assertViewHas('licencias', fn ($licencias) => $licencias->total() === 1);
    }

    public function test_license_form_displays_validation_errors(): void
    {
        $this->followingRedirects()
            ->from(route('licencia.create'))
            ->post(route('licencia.store'), [])
            ->assertOk()
            ->assertSee('El campo nombre del registro es obligatorio.')
            ->assertSee('El campo software, plataforma o servicio es obligatorio.')
            ->assertSee('El campo dependencia es obligatorio.')
            ->assertSee('El campo fecha de contratación es obligatorio.')
            ->assertSee('El campo fecha de renovación o fin es obligatorio.')
            ->assertSee('El campo costo por acceso o licencia es obligatorio.')
            ->assertSee('El campo costo total de la contratación es obligatorio.');
    }

    public function test_provider_can_be_created_from_the_license_modal(): void
    {
        $response = $this->postJson(route('licencia.proveedores.store'), [
            'nombre' => 'Canva México',
            'razon_social' => 'Canva México, S. de R.L.',
            'rfc' => 'CME260919AB1',
            'contacto_nombre' => 'Atención institucional',
            'contacto_email' => 'contacto@canva.example',
            'contacto_telefono' => '55 0000 0000',
            'sitio_web' => 'https://www.canva.com',
            'observaciones' => 'Proveedor registrado desde el modal.',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('proveedor.nombre', 'Canva México');

        $this->assertDatabaseHas('proveedores', [
            'nombre' => 'Canva México',
            'activo' => true,
        ]);

        $this->get(route('licencia.create'))
            ->assertOk()
            ->assertSee('Canva México');
    }
}
