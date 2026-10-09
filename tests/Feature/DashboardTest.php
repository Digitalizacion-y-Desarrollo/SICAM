<?php

namespace Tests\Feature;

use App\Models\Bien;
use App\Models\Categoria;
use App\Models\DepartamentoAcceso;
use App\Models\Licencia;
use App\Models\Responsable;
use App\Models\Sistema;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_live_module_counts_and_recent_activity(): void
    {
        $this->withoutVite();
        $this->actingAs(User::factory()->create());

        Responsable::create([
            'nombre' => 'Ana',
            'dependencia_id_accesos' => 'Tecnologías de la Información',
            'area_id_accesos' => 'Soporte',
            'activo' => true,
        ]);

        Sistema::create([
            'clave' => 'SIS-001',
            'nombre' => 'Sistema de pruebas',
            'descripcion' => 'Sistema para comprobar el tablero.',
            'tipo' => 'web',
            'origen' => 'interno',
            'estado' => 'produccion',
            'dependencia_id_accesos' => 'Tecnologías de la Información',
            'area_id_accesos' => 'Desarrollo',
        ]);

        Licencia::create([
            'clave' => 'LIC-0001',
            'nombre' => 'Canva institucional',
            'producto' => 'Canva',
            'tipo_licencia' => 'suscripcion',
            'cantidad_adquirida' => 5,
            'estado' => 'activa',
            'dependencia_id_accesos' => 'Comunicación Social',
            'fecha_vencimiento' => today()->addDays(15),
        ]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('1 sistemas')
            ->assertSee('1 registros')
            ->assertSee('5 accesos')
            ->assertSee('Sistema de pruebas')
            ->assertSee('Canva institucional')
            ->assertSee('Licencias que vencen en 30 días')
            ->assertSee('data-tour-title="Bienvenido a SICAM"', false)
            ->assertSee('data-tour-title="Módulos del sistema"', false)
            ->assertSee('data-tour-title="Indicadores generales"', false)
            ->assertSee('data-tour-title="Actividad reciente"', false)
            ->assertSee('data-tour-title="Requiere atención"', false)
            ->assertSee('data-tour-title="Navegación principal"', false)
            ->assertSee('data-tour-title="Búsqueda global"', false)
            ->assertSee('data-tour-title="Ayuda"', false)
            ->assertDontSee('driverObj.highlight', false)
            ->assertViewHas('metricas', fn (array $metricas) => $metricas['registros'] === 2
                && $metricas['dependencias'] === 2
                && $metricas['areas'] === 2);
    }

    public function test_dashboard_only_counts_records_from_the_users_department(): void
    {
        DepartamentoAcceso::create(['id' => 1, 'nombre' => 'Tecnologias', 'activo' => true]);
        DepartamentoAcceso::create(['id' => 2, 'nombre' => 'Sistemas', 'parent_id' => 1, 'activo' => true]);
        $this->withoutVite();
        $categoria = Categoria::create(['nombre' => 'Cómputo', 'slug' => 'computo']);

        foreach ([['Tecnologías', 'SICAM-TEC-001', 'PAT-TEC-001'], ['Obras Públicas', 'SICAM-OBR-001', 'PAT-OBR-001']] as [$dependencia, $folio, $patrimonial]) {
            Bien::create([
                'categoria_id' => $categoria->id,
                'folio_sicam' => $folio,
                'numero_patrimonial' => $patrimonial,
                'nombre' => 'Equipo '.$dependencia,
                'dependencia_id_accesos' => $dependencia,
                'area_id_accesos' => 'Sistemas',
            ]);
        }

        DepartamentoAcceso::whereKey(1)->update([
            'nombre' => Bien::where('folio_sicam', 'SICAM-TEC-001')->value('dependencia_id_accesos'),
        ]);

        $this->actingAs(User::factory()->create())
            ->withSession([
                'accesos.permissions' => ['dashboard-sicam.ver', 'bienes.ver'],
                'accesos.roles' => [],
                'accesos.departamento' => [
                    'departamento_padre' => ['id' => 1, 'nombre' => 'Nombre remoto no confiable'],
                    'departamento_hijo' => ['id' => 2, 'nombre' => 'Nombre remoto no confiable'],
                ],
            ])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('conteos', fn (array $conteos) => $conteos['bienes'] === 1)
            ->assertViewHas('metricas', fn (array $metricas) => $metricas['registros'] === 1);
    }
}
