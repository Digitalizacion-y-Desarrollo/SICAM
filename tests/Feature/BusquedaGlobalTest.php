<?php

namespace Tests\Feature;

use App\Models\Licencia;
use App\Models\Sistema;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusquedaGlobalTest extends TestCase
{
    use RefreshDatabase;

    public function test_busqueda_solo_devuelve_resultados_de_modulos_autorizados(): void
    {
        Sistema::create([
            'clave' => 'SIS-TESORERIA',
            'nombre' => 'Sistema de Tesorería',
            'descripcion' => 'Control de ingresos municipales',
            'tipo' => 'web',
            'origen' => 'interno',
            'estado' => 'produccion',
            'dependencia_id_accesos' => 'Tesorería',
        ]);

        Licencia::create([
            'clave' => 'LIC-TESORERIA',
            'nombre' => 'Licencia de Tesorería',
            'producto' => 'Suite financiera',
            'tipo_licencia' => 'perpetua',
            'cantidad_adquirida' => 1,
            'estado' => 'activa',
        ]);

        $this->actingAs(User::factory()->create())
            ->withSession([
                'accesos.permissions' => ['sistemas.ver'],
                'accesos.roles' => [],
            ]);

        $this->getJson(route('busqueda.global', ['q' => 'Tesorería']))
            ->assertOk()
            ->assertJsonCount(1, 'resultados')
            ->assertJsonPath('resultados.0.tipo', 'Sistema')
            ->assertJsonPath('resultados.0.titulo', 'Sistema de Tesorería')
            ->assertJsonMissing(['titulo' => 'Licencia de Tesorería']);
    }

    public function test_busqueda_requiere_algun_permiso_de_lectura(): void
    {
        $this->actingAs(User::factory()->create())
            ->withSession([
                'accesos.permissions' => ['dashboard-sicam.ver'],
                'accesos.roles' => [],
            ]);

        $this->getJson(route('busqueda.global', ['q' => 'sistema']))
            ->assertForbidden();
    }

    public function test_encabezado_muestra_el_activador_y_dialogo_de_busqueda(): void
    {
        $this->actingAs(User::factory()->create())
            ->withSession([
                'accesos.permissions' => ['sistemas.ver'],
                'accesos.roles' => [],
            ]);

        $this->get(route('software.sistemas'))
            ->assertOk()
            ->assertSee('data-global-search-open', false)
            ->assertSee('data-global-search-dialog', false)
            ->assertSee('Ctrl K');
    }

    public function test_invitado_no_puede_usar_la_busqueda(): void
    {
        $this->get(route('busqueda.global', ['q' => 'sistema']))
            ->assertRedirect(route('login'));
    }
}
