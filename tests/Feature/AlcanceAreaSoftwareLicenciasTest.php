<?php

namespace Tests\Feature;

use App\Models\DepartamentoAcceso;
use App\Models\Licencia;
use App\Models\Sistema;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlcanceAreaSoftwareLicenciasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_usuario_solo_ve_sistemas_y_licencias_de_su_area(): void
    {
        [$sistemaPropio, $sistemaAjeno] = $this->crearSistemas();
        $this->crearLicencias();

        $this->actingAs(User::factory()->create())
            ->withSession($this->sesion(['sistemas.ver', 'licencias.ver']));

        $this->get(route('software.sistemas'))
            ->assertOk()
            ->assertSee($sistemaPropio->nombre)
            ->assertDontSee($sistemaAjeno->nombre);

        $this->get(route('licencia.index'))
            ->assertOk()
            ->assertSee('Licencia propia')
            ->assertDontSee('Licencia ajena');

        $this->get(route('software.sistemas.show', $sistemaAjeno))->assertNotFound();
    }

    public function test_superuser_puede_consultar_los_registros_de_cualquier_area(): void
    {
        [, $sistemaAjeno] = $this->crearSistemas();
        $this->crearLicencias();

        $this->actingAs(User::factory()->create())
            ->withSession($this->sesion(['superuser']));

        $this->get(route('software.sistemas'))
            ->assertOk()
            ->assertSee($sistemaAjeno->nombre);

        $this->get(route('licencia.index'))
            ->assertOk()
            ->assertSee('Licencia ajena');
    }

    private function crearSistemas(): array
    {
        return [
            Sistema::create($this->datosSistema('SIS-PROPIO', 'Sistema propio', 'Sistemas')),
            Sistema::create($this->datosSistema('SIS-AJENO', 'Sistema ajeno', 'Obras públicas')),
        ];
    }

    private function crearLicencias(): void
    {
        foreach ([['LIC-PROPIA', 'Licencia propia', 'Sistemas'], ['LIC-AJENA', 'Licencia ajena', 'Obras públicas']] as [$clave, $nombre, $area]) {
            Licencia::create([
                'clave' => $clave,
                'nombre' => $nombre,
                'producto' => 'Producto de prueba',
                'tipo_licencia' => 'perpetua',
                'dependencia_id_accesos' => 'Tecnologías',
                'area_id_accesos' => $area,
            ]);
        }
    }

    private function datosSistema(string $clave, string $nombre, string $area): array
    {
        return [
            'clave' => $clave,
            'nombre' => $nombre,
            'descripcion' => 'Descripción de prueba',
            'tipo' => 'web',
            'origen' => 'interno',
            'estado' => 'produccion',
            'dependencia_id_accesos' => 'Tecnologías',
            'area_id_accesos' => $area,
        ];
    }

    private function sesion(array $roles): array
    {
        DepartamentoAcceso::query()->firstOrCreate(['id' => 2], ['nombre' => 'Sistemas', 'activo' => true]);

        return [
            'accesos.permissions' => ['sistemas.ver', 'licencias.ver'],
            'accesos.roles' => $roles,
            'accesos.departamento' => [
                'departamento_hijo' => ['id' => 2, 'nombre' => 'Sistemas'],
            ],
        ];
    }
}
