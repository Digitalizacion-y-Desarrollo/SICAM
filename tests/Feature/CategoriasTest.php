<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\User;
use Tests\TestCase;

class CategoriasTest extends TestCase
{
    public function test_selection_field_accepts_individual_option_inputs(): void
    {
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);
        $this->artisan('migrate', ['--database' => 'sqlite', '--force' => true])->assertExitCode(0);
        $this->withoutVite();
        $this->actingAs(User::factory()->create());

        $categoria = Categoria::create(['nombre' => 'Equipo', 'slug' => 'equipo']);

        $this->get(route('patrimonio.categorias', ['categoria' => $categoria]))
            ->assertOk()
            ->assertSee('data-campo-opciones', false)
            ->assertSee('name="opciones[]"', false)
            ->assertSee('data-agregar-opcion', false);

        $this->post(route('patrimonio.categorias.campos.store', $categoria), [
            'nombre' => 'Sistema operativo',
            'tipo' => 'SELECCION',
            'opciones' => [' Windows ', 'Linux'],
        ])->assertSessionHasNoErrors();

        $this->assertSame(['Windows', 'Linux'], $categoria->campos()->sole()->opciones);
    }

    public function test_categories_view_counts_only_active_assets_from_bienes_table(): void
    {
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);
        $this->artisan('migrate', ['--database' => 'sqlite', '--force' => true])->assertExitCode(0);
        $this->withoutVite();
        $this->actingAs(User::factory()->create());

        $categoria = Categoria::create(['nombre' => 'Mobiliario', 'slug' => 'mobiliario']);
        $otraCategoria = Categoria::create(['nombre' => 'Equipo', 'slug' => 'equipo']);

        foreach ([$categoria, $categoria, $otraCategoria] as $index => $owner) {
            $bien = $owner->bienes()->create([
                'folio_sicam' => 'SICAM-'.$index,
                'numero_patrimonial' => 'PAT-'.$index,
                'nombre' => 'Bien de prueba '.$index,
                'dependencia_id_accesos' => '1',
                'area_id_accesos' => '1',
            ]);

            if ($index === 1) {
                $bien->delete();
            }
        }

        $this->get(route('patrimonio.categorias', ['categoria' => $categoria->id]))
            ->assertOk()
            ->assertViewHas('categorias', fn ($categorias) => $categorias->firstWhere('id', $categoria->id)->bienes_count === 1
            )
            ->assertViewHas('seleccionada', fn ($seleccionada) => $seleccionada->id === $categoria->id && $seleccionada->bienes_count === 1
            );
    }
}
