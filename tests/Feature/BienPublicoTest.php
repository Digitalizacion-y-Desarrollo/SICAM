<?php

namespace Tests\Feature;

use App\Models\Bien;
use App\Models\Categoria;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BienPublicoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $this->artisan('migrate', ['--database' => 'sqlite', '--force' => true])->assertExitCode(0);
        $this->withoutVite();
    }

    public function test_public_record_shows_custom_category_fields_instead_of_legacy_brand_and_model(): void
    {
        $categoria = Categoria::create(['nombre' => 'Cómputo', 'slug' => 'computo']);
        $marca = $categoria->campos()->create(['nombre' => 'Marca', 'clave' => 'marca', 'tipo' => 'TEXTO']);
        $modelo = $categoria->campos()->create(['nombre' => 'Modelo', 'clave' => 'modelo', 'tipo' => 'TEXTO']);
        $bien = Bien::create([
            'categoria_id' => $categoria->id,
            'folio_sicam' => 'SICAM-2026-000001',
            'numero_patrimonial' => 'PAT-2026-000001',
            'nombre' => 'Computadora de prueba',
            'dependencia_id_accesos' => 1,
            'estado' => 'DISPONIBLE',
        ]);
        $bien->valores()->createMany([
            ['campo_categoria_id' => $marca->id, 'valor' => 'Lenovo'],
            ['campo_categoria_id' => $modelo->id, 'valor' => 'ThinkCentre M70q'],
        ]);

        $this->get(route('bienes.publico', $bien->public_id))
            ->assertOk()
            ->assertSee('Marca')
            ->assertSee('Lenovo')
            ->assertSee('Modelo')
            ->assertSee('ThinkCentre M70q');
    }
}
