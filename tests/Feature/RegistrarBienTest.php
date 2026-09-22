<?php

namespace Tests\Feature;

use App\Models\Bien;
use App\Models\Categoria;
use App\Models\Responsable;
use App\Models\User;
use App\Services\RegistrarBien;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RegistrarBienTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $this->artisan('migrate', ['--database' => 'sqlite', '--force' => true])->assertExitCode(0);
        $this->withoutVite();
        Storage::fake('local');
        $this->actingAs(User::factory()->create());
    }

    private function payload(array $overrides = []): array
    {
        $categoria = Categoria::firstOrCreate(['slug' => 'computo'], ['nombre' => 'Cómputo']);

        return array_replace([
            'categoria_id' => $categoria->id, 'nombre' => 'Equipo de prueba',
            'dependencia_id_accesos' => 'DEP-1', 'area_id_accesos' => 'AREA-1',
            'responsabilidad' => 'ninguna', 'estado' => 'DISPONIBLE',
        ], $overrides);
    }

    private function responsable(array $overrides = []): Responsable
    {
        return Responsable::create(array_replace([
            'nombre' => 'Ana', 'dependencia_id_accesos' => 'DEP-1', 'area_id_accesos' => 'AREA-1',
        ], $overrides));
    }

    public function test_form_uses_active_categories_fields_and_responsibles(): void
    {
        $this->payload();
        $this->responsable();
        Categoria::create(['nombre' => 'Oculta', 'slug' => 'oculta', 'activo' => false]);
        $this->get(route('patrimonio.bienes.create'))->assertOk()->assertSee('Cómputo')->assertSee('Ana')->assertDontSee('Oculta');
    }

    public function test_saves_unassigned_asset_photo_and_history_and_shows_it_in_list(): void
    {
        $this->post(route('patrimonio.bienes.store'), $this->payload([
            'fotografia' => UploadedFile::fake()->image('equipo.jpg'), 'costo' => '125.50',
            'folio_sicam' => 'FORZADO', 'numero_patrimonial' => 'FORZADO',
        ]))->assertSessionHasNoErrors()->assertRedirect(route('patrimonio.bienes'));
        $bien = Bien::sole();
        $this->assertSame('SICAM-'.now()->year.'-000001', $bien->folio_sicam);
        $this->assertSame('PAT-'.now()->year.'-000001', $bien->numero_patrimonial);
        Storage::disk('local')->assertExists($bien->fotografia_path);
        $this->assertDatabaseCount('asignaciones', 0);
        $this->assertDatabaseHas('movimientos_bien', ['bien_id' => $bien->id, 'tipo' => 'ALTA']);
        $this->get(route('patrimonio.bienes'))->assertOk()->assertSee($bien->nombre)->assertSee($bien->folio_sicam);
        $this->get(route('patrimonio.bienes', ['buscar' => 'inexistente']))->assertOk()->assertDontSee($bien->folio_sicam);
    }

    public function test_asset_list_combines_category_status_and_location_filters(): void
    {
        $this->post(route('patrimonio.bienes.store'), $this->payload([
            'nombre' => 'Computadora filtrable',
            'dependencia_id_accesos' => 'Administración',
            'area_id_accesos' => 'Sistemas',
        ]))->assertSessionHasNoErrors();
        $computadora = Bien::sole();

        $otraCategoria = Categoria::create(['nombre' => 'Mobiliario', 'slug' => 'mobiliario']);
        $this->post(route('patrimonio.bienes.store'), $this->payload([
            'categoria_id' => $otraCategoria->id,
            'nombre' => 'Escritorio excluido',
            'dependencia_id_accesos' => 'Tesorería',
            'area_id_accesos' => 'Egresos',
            'responsabilidad' => 'area',
            'estado' => 'ASIGNADO',
        ]))->assertSessionHasNoErrors();
        $escritorio = Bien::where('nombre', 'Escritorio excluido')->sole();

        $this->get(route('patrimonio.bienes', [
            'buscar' => 'filtrable',
            'categoria_id' => $computadora->categoria_id,
            'estado' => 'DISPONIBLE',
            'dependencia' => 'Administración',
            'area' => 'Sistemas',
        ]))->assertOk()
            ->assertSee($computadora->folio_sicam)
            ->assertDontSee($escritorio->nombre)
            ->assertSee('1 bien encontrado');
    }

    public function test_saves_category_values_and_person_assignment(): void
    {
        $data = $this->payload(['responsabilidad' => 'persona', 'estado' => 'ASIGNADO', 'responsable_id' => $this->responsable()->id]);
        $campo = Categoria::find($data['categoria_id'])->campos()->create(['nombre' => 'RAM', 'clave' => 'ram', 'tipo' => 'NUMERO', 'requerido' => true]);
        $data['campos'] = [$campo->id => '16'];
        $this->post(route('patrimonio.bienes.store'), $data)->assertSessionHasNoErrors();
        $bien = Bien::sole();
        $this->assertDatabaseHas('valores_bien', ['bien_id' => $bien->id, 'campo_categoria_id' => $campo->id, 'valor' => '16']);
        $this->assertDatabaseHas('asignaciones', ['bien_id' => $bien->id, 'tipo_responsabilidad' => 'PERSONA', 'responsable_id' => $data['responsable_id']]);
        $this->get(route('patrimonio.bienes'))->assertOk()->assertSee('Ana');
    }

    public function test_asset_can_be_registered_for_responsible_without_area(): void
    {
        $responsable = $this->responsable(['area_id_accesos' => null]);

        $this->post(route('patrimonio.bienes.store'), $this->payload([
            'area_id_accesos' => null,
            'responsabilidad' => 'persona',
            'estado' => 'ASIGNADO',
            'responsable_id' => $responsable->id,
        ]))->assertSessionHasNoErrors();

        $bien = Bien::sole();
        $this->assertNull($bien->area_id_accesos);
        $this->assertDatabaseHas('asignaciones', [
            'bien_id' => $bien->id,
            'responsable_id' => $responsable->id,
            'area_id_accesos' => null,
        ]);
    }

    public function test_existing_asset_can_be_assigned_to_responsible_without_area(): void
    {
        $this->post(route('patrimonio.bienes.store'), $this->payload([
            'area_id_accesos' => null,
        ]))->assertSessionHasNoErrors();
        $bien = Bien::sole();
        $responsable = $this->responsable(['area_id_accesos' => null]);

        $this->post(route('patrimonio.asignaciones.store'), [
            'bien_id' => $bien->id,
            'responsabilidad' => 'persona',
            'responsable_id' => $responsable->id,
            'dependencia_id_accesos' => $responsable->dependencia_id_accesos,
            'estado' => 'ASIGNADO',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('asignaciones', [
            'bien_id' => $bien->id,
            'responsable_id' => $responsable->id,
            'area_id_accesos' => null,
        ]);
        $this->assertNull($bien->fresh()->area_id_accesos);
    }

    public function test_creates_new_responsible_with_asset_location(): void
    {
        $this->post(route('patrimonio.bienes.store'), $this->payload([
            'responsabilidad' => 'persona', 'estado' => 'EN_RESGUARDO', 'crear_responsable' => '1',
            'nuevo_responsable' => ['nombre' => 'Elena', 'numero_empleado' => '123', 'activo' => false],
        ]))->assertSessionHasNoErrors();
        $responsable = Responsable::sole();
        $this->assertTrue($responsable->activo);
        $this->assertSame('AREA-1', $responsable->area_id_accesos);
        $this->assertDatabaseHas('asignaciones', ['responsable_id' => $responsable->id]);
    }

    public function test_area_assignment_and_distinct_consecutive_numbers(): void
    {
        foreach (range(1, 2) as $number) {
            $this->post(route('patrimonio.bienes.store'), $this->payload(['responsabilidad' => 'area', 'estado' => 'ASIGNADO']))->assertSessionHasNoErrors();
        }
        $this->assertDatabaseCount('bienes', 2);
        $this->assertDatabaseHas('asignaciones', ['tipo_responsabilidad' => 'AREA', 'responsable_id' => null, 'area_id_accesos' => 'AREA-1']);
        $this->assertSame(2, Bien::distinct()->count('numero_patrimonial'));
    }

    public function test_rejects_missing_required_data_and_inconsistent_state(): void
    {
        $this->post(route('patrimonio.bienes.store'), [])->assertSessionHasErrors(['nombre', 'categoria_id', 'dependencia_id_accesos']);
        $this->post(route('patrimonio.bienes.store'), $this->payload(['estado' => 'ASIGNADO']))->assertSessionHasErrors('estado');
        $this->assertDatabaseCount('bienes', 0);
    }

    public function test_rejects_inactive_deleted_or_other_location_responsibles(): void
    {
        foreach ([['activo' => false], ['area_id_accesos' => 'OTRA'], ['dependencia_id_accesos' => 'OTRA']] as $overrides) {
            $this->post(route('patrimonio.bienes.store'), $this->payload([
                'responsabilidad' => 'persona', 'estado' => 'ASIGNADO', 'responsable_id' => $this->responsable($overrides)->id,
            ]))->assertSessionHasErrors('responsable_id');
        }
        $responsable = $this->responsable();
        $responsable->delete();
        $this->post(route('patrimonio.bienes.store'), $this->payload([
            'responsabilidad' => 'persona', 'estado' => 'ASIGNADO', 'responsable_id' => $responsable->id,
        ]))->assertSessionHasErrors('responsable_id');
        $this->assertDatabaseCount('bienes', 0);
    }

    public function test_rejects_inactive_or_deleted_category(): void
    {
        $data = $this->payload();
        $categoria = Categoria::find($data['categoria_id']);
        $categoria->update(['activo' => false]);
        $this->post(route('patrimonio.bienes.store'), $data)->assertSessionHasErrors('categoria_id');
        $categoria->update(['activo' => true]);
        $categoria->delete();
        $this->post(route('patrimonio.bienes.store'), $data)->assertSessionHasErrors('categoria_id');
        $this->assertDatabaseCount('bienes', 0);
    }

    public function test_rejects_invalid_and_foreign_category_fields(): void
    {
        $data = $this->payload();
        $categoria = Categoria::find($data['categoria_id']);
        $numero = $categoria->campos()->create(['nombre' => 'RAM', 'clave' => 'ram', 'tipo' => 'NUMERO', 'requerido' => true]);
        $seleccion = $categoria->campos()->create(['nombre' => 'SO', 'clave' => 'so', 'tipo' => 'SELECCION', 'opciones' => ['Linux', 'Windows']]);
        $fecha = $categoria->campos()->create(['nombre' => 'Fecha', 'clave' => 'fecha', 'tipo' => 'FECHA']);
        $this->post(route('patrimonio.bienes.store'), $data)->assertSessionHasErrors('campos.'.$numero->id);
        $data['campos'] = [$numero->id => 'texto', $seleccion->id => 'Otro', $fecha->id => 'no-fecha', 999 => 'ajeno'];
        $this->post(route('patrimonio.bienes.store'), $data)->assertSessionHasErrors(['campos', 'campos.'.$numero->id, 'campos.'.$seleccion->id, 'campos.'.$fecha->id]);
        $this->assertDatabaseCount('bienes', 0);
    }

    public function test_rejects_duplicate_serial_and_invalid_photo_without_writes(): void
    {
        $this->post(route('patrimonio.bienes.store'), $this->payload(['numero_serie' => 'SERIE-1']))->assertSessionHasNoErrors();
        $this->post(route('patrimonio.bienes.store'), $this->payload(['numero_serie' => 'SERIE-1']))->assertSessionHasErrors('numero_serie');
        foreach ([UploadedFile::fake()->create('archivo.pdf', 5), UploadedFile::fake()->image('grande.jpg')->size(5121)] as $photo) {
            $this->post(route('patrimonio.bienes.store'), $this->payload(['fotografia' => $photo]))->assertSessionHasErrors('fotografia');
        }
        $this->assertDatabaseCount('bienes', 1);
        $this->assertCount(0, Storage::disk('local')->allFiles('bienes'));
        $this->assertCount(1, Storage::disk('local')->allFiles('qr'));
    }

    public function test_failed_transaction_rolls_back_asset_counter_and_photo(): void
    {
        $data = $this->payload(['campos' => [999 => 'campo inexistente']]);
        try {
            app(RegistrarBien::class)->registrar($data, UploadedFile::fake()->image('equipo.jpg'), '127.0.0.1');
            $this->fail('Se esperaba el rechazo de la clave foránea.');
        } catch (QueryException) {
            $this->assertDatabaseCount('bienes', 0);
            $this->assertDatabaseCount('consecutivos', 0);
            $this->assertDatabaseCount('movimientos_bien', 0);
            $this->assertCount(0, Storage::disk('local')->allFiles());
        }
    }
}
