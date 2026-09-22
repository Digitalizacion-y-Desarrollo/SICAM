<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermisosAccesosTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_solo_puede_abrir_las_vistas_autorizadas(): void
    {
        $this->actingAs(User::factory()->create())
            ->withSession([
                'accesos.permissions' => ['licencias.ver'],
                'accesos.roles' => [],
            ]);

        $this->get(route('licencia.index'))->assertOk();
        $this->get(route('licencia.create'))->assertForbidden();
        $this->get(route('software.sistemas'))->assertForbidden();
    }

    public function test_dashboard_usa_el_nombre_registrado_en_accesos(): void
    {
        $this->actingAs(User::factory()->create())
            ->withSession([
                'accesos.permissions' => ['dashboard-sicam.ver'],
                'accesos.roles' => [],
            ]);

        $this->get(route('dashboard'))->assertOk();

        $this->withSession([
            'accesos.permissions' => ['dashboard.ver'],
            'accesos.roles' => [],
        ])->get(route('dashboard'))->assertForbidden();
    }

    public function test_acciones_de_escritura_requieren_su_permiso_especifico(): void
    {
        $this->actingAs(User::factory()->create())
            ->withSession([
                'accesos.permissions' => ['proveedores.ver'],
                'accesos.roles' => [],
            ]);

        $this->post(route('licencia.proveedores.store'), [
            'nombre' => 'Proveedor sin permiso',
        ])->assertForbidden();

        $this->assertDatabaseMissing('proveedores', ['nombre' => 'Proveedor sin permiso']);
    }

    public function test_admin_general_tiene_acceso_total(): void
    {
        $this->actingAs(User::factory()->create())
            ->withSession([
                'accesos.permissions' => [],
                'accesos.roles' => ['admin_general'],
            ]);

        $this->get(route('dashboard'))->assertOk();
        $this->get(route('licencia.create'))->assertOk();
    }

    public function test_menu_oculta_modulos_y_acciones_no_autorizadas(): void
    {
        $this->actingAs(User::factory()->create())
            ->withSession([
                'accesos.permissions' => ['licencias.ver'],
                'accesos.roles' => [],
            ]);

        $this->get(route('licencia.index'))
            ->assertOk()
            ->assertSee('Licencias')
            ->assertDontSee('Registrar licencia')
            ->assertDontSee('Sistemas')
            ->assertDontSee('Proveedores');
    }

    public function test_usuario_sin_permisos_es_enviado_a_la_vista_informativa(): void
    {
        $this->actingAs(User::factory()->create())
            ->withSession([
                'accesos.permissions' => [],
                'accesos.roles' => [],
            ]);

        $this->get(route('sin-permisos'))
            ->assertOk()
            ->assertSee('no tiene permisos asignados');
    }
}
