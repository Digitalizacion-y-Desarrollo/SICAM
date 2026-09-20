<?php

namespace Tests\Feature;

use App\Models\Responsable;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ResponsablesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $this->artisan('migrate', ['--database' => 'sqlite', '--force' => true])->assertExitCode(0);
    }

    public function test_responsible_can_be_registered_without_area(): void
    {
        $this->post(route('patrimonio.responsables.store'), [
            'nombre' => 'Responsable sin área',
            'dependencia_id_accesos' => 'DIRECCIÓN DE ADMINISTRACIÓN',
        ])->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Responsable registrado correctamente.');

        $responsable = Responsable::sole();
        $this->assertNull($responsable->area_id_accesos);
    }

    public function test_inline_registration_returns_responsible_and_validates_without_duplicates(): void
    {
        $data = ['nombre' => 'Ana', 'apellido_paterno' => 'Luna', 'numero_empleado' => 'SOFT-01', 'dependencia_id_accesos' => 'Digitalización', 'area_id_accesos' => 'Desarrollo'];
        $this->postJson(route('patrimonio.responsables.store'), $data)
            ->assertCreated()->assertJsonPath('responsable.nombre_completo', 'Ana Luna')
            ->assertJsonPath('responsable.id', Responsable::sole()->id);
        $this->postJson(route('patrimonio.responsables.store'), $data)
            ->assertUnprocessable()->assertJsonValidationErrors('numero_empleado');
        $this->postJson(route('patrimonio.responsables.store'), ['correo' => 'invalido'])
            ->assertUnprocessable()->assertJsonValidationErrors(['nombre', 'dependencia_id_accesos', 'correo']);
        $this->assertDatabaseCount('responsables', 1);
        $this->assertDatabaseHas('responsables', ['numero_empleado' => 'SOFT-01', 'dependencia_id_accesos' => 'Digitalización', 'area_id_accesos' => 'Desarrollo']);
    }

    public function test_software_form_offers_inline_registration_for_both_roles(): void
    {
        $this->withoutVite();
        $this->get(route('software.sistemas.create'))->assertOk()
            ->assertSee('data-nuevo-responsable="funcional"', false)
            ->assertSee('data-nuevo-responsable="tecnico"', false)
            ->assertSee('<dialog', false)
            ->assertSee('data-responsable-dependencia', false)
            ->assertSee('data-responsable-area', false)
            ->assertSee('Guardar responsable');
    }

    public function test_validation_errors_are_exposed_by_field_for_inline_rendering(): void
    {
        $this->withoutVite();

        $this->post(route('patrimonio.responsables.store'), [])
            ->assertSessionHasErrors(['nombre', 'dependencia_id_accesos']);

        $this->get(route('patrimonio.responsables'))
            ->assertOk()
            ->assertSee('Revisa los campos marcados antes de guardar.')
            ->assertSee('El campo nombre es obligatorio.')
            ->assertSee('El campo dependencia es obligatorio.')
            ->assertSee('"fieldErrors":{"nombre":', false)
            ->assertSee('"dependencia_id_accesos":', false);
    }
}
