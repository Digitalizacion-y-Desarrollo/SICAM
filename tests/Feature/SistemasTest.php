<?php

namespace Tests\Feature;

use App\Models\Auditoria;
use App\Models\Responsable;
use App\Models\Sistema;
use App\Models\User;
use App\Services\DepartamentosAccesos;
use App\Services\GuardarSistema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SistemasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->mock(DepartamentosAccesos::class, function ($mock) {
            $mock->shouldReceive('listar')->andReturn([
                ['id' => '1', 'nombre' => 'Digitalización', 'parent_id' => null],
                ['id' => '2', 'nombre' => 'Desarrollo', 'parent_id' => '1'],
                ['id' => '3', 'nombre' => 'Tesorería', 'parent_id' => null],
                ['id' => '4', 'nombre' => 'Ingresos', 'parent_id' => '3'],
            ]);
        });
    }

    public function test_registration_form_uses_registered_software_routes(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get(route('software.sistemas.create'))
            ->assertOk()
            ->assertSee('action="'.route('software.sistemas.store').'"', false)
            ->assertSee('href="'.route('software.sistemas').'"', false);

        $software = new Sistema;
        $software->id = 1;
        $this->view('software.sistemas.create', ['software' => $software])
            ->assertSee('action="'.route('software.sistemas.update', $software).'"', false);
    }

    public function test_software_summary_uses_live_system_metrics(): void
    {
        $this->actingAs(User::factory()->create());
        app(GuardarSistema::class)->ejecutar($this->datos([
            'clave' => 'SIS-PROD',
            'nombre' => 'Portal ciudadano',
            'estado' => 'produccion',
        ]));
        app(GuardarSistema::class)->ejecutar($this->datos([
            'clave' => 'SIS-TEST',
            'nombre' => 'Gestión interna',
            'estado' => 'pruebas',
        ]));

        $this->get(route('software.resumen'))
            ->assertOk()
            ->assertSee('Resumen')
            ->assertSee('Portal ciudadano')
            ->assertSee('Gestión interna')
            ->assertViewHas('metricas', fn (array $metricas) => $metricas['total'] === 2 && $metricas['produccion'] === 1 && $metricas['en_proceso'] === 1 && $metricas['sin_responsable'] === 2);
    }

    private function datos(array $cambios = []): array
    {
        return array_replace([
            'clave' => 'SIS-CONV', 'nombre' => 'Sistema de convocatorias',
            'descripcion' => 'Administra convocatorias municipales.', 'objetivo' => null,
            'tipo' => 'web', 'origen' => 'interno', 'estado' => 'desarrollo',
            'dependencia_id_accesos' => 'Digitalización', 'area_id_accesos' => 'Desarrollo',
            'responsable_funcional_id' => null, 'responsable_tecnico_id' => null,
            'fecha_inicio' => '2026-01-01', 'fecha_liberacion' => null,
            'url_produccion' => null, 'repositorio_url' => null,
        ], $cambios);
    }

    public function test_guest_cannot_read_create_or_update_systems(): void
    {
        $sistema = app(GuardarSistema::class)->ejecutar($this->datos());
        foreach (['software.sistemas', 'software.sistemas.create', 'software.sistemas.show', 'software.sistemas.edit'] as $ruta) {
            $this->get(route($ruta, str_ends_with($ruta, 'show') || str_ends_with($ruta, 'edit') ? $sistema : []))->assertRedirect(route('login'));
        }
        $this->post(route('software.sistemas.store'), $this->datos(['clave' => 'OTRO']))->assertRedirect(route('login'));
        $this->put(route('software.sistemas.update', $sistema), $this->datos(['estado' => 'produccion']))->assertRedirect(route('login'));
        $this->assertDatabaseCount('sistemas', 1);
        $this->assertSame('desarrollo', $sistema->fresh()->estado);
    }

    public function test_create_update_and_audit_with_current_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->post(route('software.sistemas.store'), $this->datos(['clave' => ' sis-conv ']))->assertSessionHasNoErrors();
        $sistema = Sistema::sole();
        $this->assertSame('SIS-CONV', $sistema->clave);
        $this->put(route('software.sistemas.update', $sistema), $this->datos(['estado' => 'produccion', 'fecha_liberacion' => '2026-02-01']))->assertSessionHasNoErrors()->assertRedirect(route('software.sistemas.show', $sistema));
        $this->assertSame('produccion', $sistema->fresh()->estado);
        $audit = Auditoria::where('entidad', 'sistemas')->where('accion', 'ACTUALIZAR')->sole();
        $this->assertSame('desarrollo', $audit->valores_anteriores['estado']);
        $this->assertSame('produccion', $audit->valores_nuevos['estado']);
        $this->assertEquals($user->id, $audit->usuario_id_accesos);
        $this->get(route('software.sistemas.show', $sistema))->assertOk()->assertSee('Historial de cambios')->assertSee($user->name);
        $this->get(route('software.sistemas.edit', $sistema))->assertOk()->assertSee('Guardar cambios');
        $this->get(route('software.sistemas.create'))->assertOk()->assertSee('Guardar sistema');
    }

    public function test_creation_persists_selected_responsibles(): void
    {
        $this->actingAs(User::factory()->create());
        $funcional = Responsable::create([
            'nombre' => 'Ana',
            'dependencia_id_accesos' => 'Digitalización',
            'activo' => true,
        ]);
        $tecnico = Responsable::create([
            'nombre' => 'Luis',
            'dependencia_id_accesos' => 'Digitalización',
            'activo' => true,
        ]);

        $this->post(route('software.sistemas.store'), $this->datos([
            'responsable_funcional_id' => $funcional->id,
            'responsable_tecnico_id' => $tecnico->id,
        ]))->assertSessionHasNoErrors();

        $sistema = Sistema::sole();
        $this->assertSame($funcional->id, $sistema->responsable_funcional_id);
        $this->assertSame($tecnico->id, $sistema->responsable_tecnico_id);
    }

    public function test_system_detail_displays_a_field_by_field_audit_log(): void
    {
        $user = User::factory()->create(['name' => 'María Auditora']);
        $this->actingAs($user);
        $sistema = app(GuardarSistema::class)->ejecutar($this->datos());

        app(GuardarSistema::class)->ejecutar($this->datos([
            'nombre' => 'Sistema actualizado',
            'estado' => 'produccion',
        ]), $sistema);

        $this->get(route('software.sistemas.show', $sistema))
            ->assertOk()
            ->assertSee('Últimas actualizaciones')
            ->assertSee('Creación')
            ->assertSee('Actualización')
            ->assertSee('Nombre')
            ->assertSee('Sistema de convocatorias')
            ->assertSee('Sistema actualizado')
            ->assertSee('Estado')
            ->assertSee('En desarrollo')
            ->assertSee('En producción')
            ->assertSee('María Auditora');
    }

    public function test_invalid_values_and_duplicate_key_do_not_write_data(): void
    {
        $this->actingAs(User::factory()->create());
        app(GuardarSistema::class)->ejecutar($this->datos());
        $this->post(route('software.sistemas.store'), $this->datos([
            'estado' => 'invalido', 'tipo' => 'invalido', 'origen' => 'invalido',
            'url_produccion' => 'javascript:alert(1)', 'fecha_liberacion' => '2025-01-01', 'responsable_tecnico_id' => 999,
        ]))->assertSessionHasErrors(['clave', 'estado', 'tipo', 'origen', 'url_produccion', 'fecha_liberacion', 'responsable_tecnico_id']);
        $this->assertDatabaseCount('sistemas', 1);
        $this->assertSame(1, Auditoria::where('entidad', 'sistemas')->count());
    }

    public function test_department_area_and_url_credentials_are_validated(): void
    {
        $this->actingAs(User::factory()->create());
        $this->post(route('software.sistemas.store'), $this->datos(['area_id_accesos' => 'Ingresos']))->assertSessionHasErrors('area_id_accesos');
        $this->post(route('software.sistemas.store'), $this->datos(['dependencia_id_accesos' => 'Inventada']))->assertSessionHasErrors('dependencia_id_accesos');
        $this->post(route('software.sistemas.store'), $this->datos(['repositorio_url' => 'https://usuario:clave@example.com/repo']))->assertSessionHasErrors('repositorio_url');
        $this->assertDatabaseCount('sistemas', 0);
        $this->post(route('software.sistemas.store'), $this->datos(['area_id_accesos' => null]))->assertSessionHasNoErrors();
        $this->assertNull(Sistema::sole()->area_id_accesos);
    }

    public function test_inactive_responsible_can_be_preserved_but_not_newly_assigned(): void
    {
        $this->actingAs(User::factory()->create());
        $responsable = Responsable::create(['nombre' => 'Ana', 'dependencia_id_accesos' => 'Digitalización', 'activo' => false]);
        $this->post(route('software.sistemas.store'), $this->datos(['responsable_tecnico_id' => $responsable->id]))->assertSessionHasErrors('responsable_tecnico_id');
        $sistema = app(GuardarSistema::class)->ejecutar($this->datos(['responsable_tecnico_id' => $responsable->id]));
        $this->put(route('software.sistemas.update', $sistema), $this->datos(['responsable_tecnico_id' => $responsable->id, 'estado' => 'descontinuado']))->assertSessionHasNoErrors();
        $this->assertSame('descontinuado', $sistema->fresh()->estado);
    }

    public function test_catalog_outage_preserves_existing_location_but_blocks_new_ones(): void
    {
        $this->actingAs(User::factory()->create());
        $this->mock(DepartamentosAccesos::class, fn ($mock) => $mock->shouldReceive('listar')->andReturn([]));
        $this->post(route('software.sistemas.store'), $this->datos())->assertSessionHasErrors('dependencia_id_accesos');
        $sistema = app(GuardarSistema::class)->ejecutar($this->datos());
        $this->put(route('software.sistemas.update', $sistema), $this->datos(['estado' => 'pruebas']))->assertSessionHasNoErrors();
        $this->put(route('software.sistemas.update', $sistema), $this->datos(['area_id_accesos' => 'Otra']))->assertSessionHasErrors('dependencia_id_accesos');
    }

    public function test_filter_pagination_empty_state_and_escaped_content(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get(route('software.sistemas'))->assertOk()->assertSee('Aún no hay sistemas registrados.');
        foreach (range(1, 17) as $i) {
            app(GuardarSistema::class)->ejecutar($this->datos(['clave' => 'SIS-'.$i, 'nombre' => 'Sistema '.$i, 'estado' => 'produccion']));
        }
        $this->get(route('software.sistemas', ['estado' => 'produccion', 'page' => 2]))->assertOk()->assertViewHas('sistemas', fn ($items) => $items->count() === 2 && $items->total() === 17);
        $this->get(route('software.sistemas', ['buscar' => 'SIS-17', 'tipo' => 'web', 'dependencia' => 'Digitalización']))->assertOk()->assertViewHas('sistemas', fn ($items) => $items->total() === 1);
        $this->get(route('software.sistemas', ['estado' => 'suspendido']))->assertOk()->assertSee('No hay sistemas que coincidan con los filtros.');
        $sistema = app(GuardarSistema::class)->ejecutar($this->datos(['clave' => 'XSS', 'nombre' => '<script>alert(1)</script>']));
        $this->get(route('software.sistemas.show', $sistema))->assertOk()->assertDontSee('<script>alert(1)</script>', false)->assertSee('&lt;script&gt;', false);
    }

    public function test_audit_failure_rolls_back_system_creation(): void
    {
        Auditoria::creating(fn () => throw new \RuntimeException('Auditoría no disponible'));
        try {
            app(GuardarSistema::class)->ejecutar($this->datos());
            $this->fail('Se esperaba el fallo de auditoría.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Auditoría no disponible', $exception->getMessage());
            $this->assertDatabaseCount('sistemas', 0);
        } finally {
            Auditoria::flushEventListeners();
        }
    }
}
