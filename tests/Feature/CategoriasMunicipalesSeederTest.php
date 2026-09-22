<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\User;
use Database\Seeders\CategoriasMunicipalesSeeder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CategoriasMunicipalesSeederTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $this->artisan('migrate', ['--database' => 'sqlite', '--force' => true])->assertExitCode(0);
        $this->withoutVite();
        $this->actingAs(User::factory()->create());
    }

    public function test_seeder_is_repeatable_and_preserves_custom_field_configuration(): void
    {
        $this->seed(CategoriasMunicipalesSeeder::class);
        $categoria = Categoria::where('slug', 'computo')->sole();
        $campo = $categoria->campos()->where('clave', 'tipo_equipo')->sole();
        $campo->update(['nombre' => 'Tipo personalizado', 'opciones' => ['Equipo especial'], 'requerido' => false]);
        $this->seed(CategoriasMunicipalesSeeder::class);
        $this->assertDatabaseCount('categorias', 3);
        $this->assertDatabaseCount('campos_categoria', 41);
        $this->assertSame('Tipo personalizado', $campo->fresh()->nombre);
        $this->assertSame(['Equipo especial'], $campo->fresh()->opciones);
        $this->assertFalse($campo->fresh()->requerido);
    }

    public function test_reuses_category_created_from_ui_and_preserves_inactive_or_deleted_categories(): void
    {
        $existente = Categoria::create(['nombre' => 'Cómputo', 'slug' => 'computo-abcde']);
        $inactiva = Categoria::create(['nombre' => 'Telefonía', 'slug' => 'telefonia', 'activo' => false]);
        $eliminada = Categoria::create(['nombre' => 'Mobiliario', 'slug' => 'mobiliario']);
        $eliminada->delete();
        $this->seed(CategoriasMunicipalesSeeder::class);
        $this->assertDatabaseCount('categorias', 3);
        $this->assertSame(14, $existente->campos()->count());
        $this->assertFalse($inactiva->fresh()->activo);
        $this->assertTrue($eliminada->fresh()->trashed());
        $this->assertSame(0, $inactiva->campos()->count());
    }

    public function test_seeded_fields_render_and_allow_asset_registration_for_each_category(): void
    {
        $this->seed(CategoriasMunicipalesSeeder::class);
        $response = $this->get(route('patrimonio.bienes.create'))->assertOk();

        foreach (Categoria::with('campos')->get() as $categoria) {
            $response->assertSee($categoria->nombre);
            $valores = [];
            foreach ($categoria->campos as $campo) {
                $response->assertSee($campo->nombre);
                $valores[$campo->id] = match ($campo->tipo) {
                    'SELECCION' => $campo->opciones[0],
                    'NUMERO' => '16',
                    'FECHA' => '2026-09-09',
                    default => 'Dato de prueba',
                };
            }
            $this->post(route('patrimonio.bienes.store'), [
                'categoria_id' => $categoria->id, 'nombre' => 'Bien de prueba '.$categoria->nombre,
                'dependencia_id_accesos' => 'DEP-1', 'area_id_accesos' => 'AREA-1',
                'responsabilidad' => 'ninguna', 'estado' => 'DISPONIBLE', 'campos' => $valores,
            ])->assertSessionHasNoErrors()->assertRedirect(route('patrimonio.bienes'));
        }

        $this->assertDatabaseCount('bienes', 3);
        $this->assertDatabaseCount('valores_bien', 41);
    }

    public function test_corrects_previous_real_estate_category_without_losing_records(): void
    {
        $categoria = Categoria::create(['nombre' => 'Inmobiliaria', 'slug' => 'inmobiliaria']);
        $anterior = $categoria->campos()->create(['clave' => 'clave_catastral', 'nombre' => 'Clave catastral', 'tipo' => 'TEXTO', 'requerido' => true]);
        $bien = $categoria->bienes()->create([
            'nombre' => 'Escritorio existente', 'folio_sicam' => 'SICAM-TEST', 'numero_patrimonial' => 'PAT-TEST',
            'dependencia_id_accesos' => 'DEP-1', 'area_id_accesos' => 'AREA-1',
        ]);
        DB::table('valores_bien')->insert(['bien_id' => $bien->id, 'campo_categoria_id' => $anterior->id, 'valor' => 'Dato previo']);

        $this->seed(CategoriasMunicipalesSeeder::class);
        $this->seed(CategoriasMunicipalesSeeder::class);

        $this->assertSame('mobiliario', $categoria->fresh()->slug);
        $this->assertSame($categoria->id, $bien->fresh()->categoria_id);
        $this->assertFalse($anterior->fresh()->activo);
        $this->assertFalse($anterior->fresh()->requerido);
        $this->assertDatabaseHas('valores_bien', ['campo_categoria_id' => $anterior->id, 'valor' => 'Dato previo']);
        $this->assertSame(14, $categoria->campos()->where('activo', true)->count());
        $this->get(route('patrimonio.bienes.create'))->assertOk()
            ->assertSee('Mobiliario')->assertSee('Escritorio')->assertSee('Silla operativa')
            ->assertDontSee('Clave catastral')->assertDontSee('Inmobiliaria');
    }
}
