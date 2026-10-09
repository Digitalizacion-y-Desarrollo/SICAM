<?php

namespace Tests\Feature;

use App\Models\Bien;
use App\Models\Categoria;
use App\Models\DepartamentoAcceso;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AlcanceDepartamentoPatrimonioTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $this->artisan('migrate', ['--database' => 'sqlite', '--force' => true])->assertExitCode(0);
        $this->withoutVite();
    }

    public function test_regular_user_sees_all_areas_in_own_department_only(): void
    {
        DepartamentoAcceso::insert([
            ['id' => 10, 'nombre' => 'Tecnologías', 'parent_id' => null, 'activo' => true],
            ['id' => 11, 'nombre' => 'Desarrollo', 'parent_id' => 10, 'activo' => true],
            ['id' => 12, 'nombre' => 'Soporte', 'parent_id' => 10, 'activo' => true],
            ['id' => 20, 'nombre' => 'Obras públicas', 'parent_id' => null, 'activo' => true],
        ]);
        $categoria = Categoria::create(['nombre' => 'Cómputo', 'slug' => 'computo']);
        foreach ([[10, 11, 'Bien de Desarrollo'], [10, 12, 'Bien de Soporte'], [20, null, 'Bien ajeno']] as [$dependencia, $area, $nombre]) {
            Bien::create([
                'categoria_id' => $categoria->id,
                'folio_sicam' => 'SICAM-'.str($nombre)->slug(),
                'numero_patrimonial' => 'PAT-'.str($nombre)->slug(),
                'nombre' => $nombre,
                'dependencia_id_accesos' => $dependencia,
                'area_id_accesos' => $area,
            ]);
        }

        $this->actingAs(User::factory()->create())
            ->withSession([
                'accesos.roles' => [],
                'accesos.permissions' => ['bienes.ver'],
                'accesos.departamento' => ['departamento_padre' => ['id' => 10]],
            ])
            ->get(route('patrimonio.bienes'))
            ->assertOk()
            ->assertSee('Bien de Desarrollo')
            ->assertSee('Bien de Soporte')
            ->assertDontSee('Bien ajeno');
    }
}
