<?php

namespace Tests\Feature;

use App\Models\Bien;
use App\Models\Categoria;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FiltroBienesPorDepartamentoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $this->artisan('migrate', ['--database' => 'sqlite', '--force' => true])->assertExitCode(0);
        $this->withoutVite();
    }

    public function test_usuario_regular_ve_solo_su_dependencia_y_no_puede_cambiar_el_filtro(): void
    {
        $this->crearBienes();

        $this->actingAs(User::factory()->create())
            ->withSession($this->sesion(['bienes.ver']))
            ->get(route('patrimonio.bienes', ['dependencia' => 'DIRECCIÓN DE OBRAS PÚBLICAS']))
            ->assertOk()
            ->assertSee('Equipo de Tecnologías')
            ->assertDontSee('Equipo de otra dependencia')
            ->assertSee('disabled', false);
    }

    public function test_superuser_puede_cambiar_el_filtro_de_dependencia(): void
    {
        $this->crearBienes();

        $this->actingAs(User::factory()->create())
            ->withSession($this->sesion(['superuser']))
            ->get(route('patrimonio.bienes', ['dependencia' => 'DIRECCIÓN DE OBRAS PÚBLICAS']))
            ->assertOk()
            ->assertSee('Equipo de otra dependencia')
            ->assertDontSee('Equipo de Tecnologías');
    }

    private function crearBienes(): void
    {
        $categoria = Categoria::create(['nombre' => 'Cómputo', 'slug' => 'computo']);

        foreach ([
            ['DIRECCIÓN DE TECNOLOGÍAS', 'SICAM-TEC-001', 'PAT-TEC-001', 'Equipo de Tecnologías'],
            ['DIRECCIÓN DE OBRAS PÚBLICAS', 'SICAM-OTRA-001', 'PAT-OTRA-001', 'Equipo de otra dependencia'],
        ] as [$dependencia, $folio, $patrimonial, $nombre]) {
            Bien::create([
                'categoria_id' => $categoria->id,
                'folio_sicam' => $folio,
                'numero_patrimonial' => $patrimonial,
                'nombre' => $nombre,
                'dependencia_id_accesos' => $dependencia,
                'area_id_accesos' => 'AREA-1',
            ]);
        }
    }

    private function sesion(array $roles): array
    {
        return [
            'accesos.permissions' => ['bienes.ver'],
            'accesos.roles' => $roles,
            'accesos.departamento' => [
                'departamento_padre' => ['id' => 21, 'nombre' => 'DIRECCIÓN DE TECNOLOGÍAS'],
            ],
        ];
    }
}
