<?php

namespace Tests\Feature;

use App\Models\Licencia;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProveedorCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_indice_muestra_acciones_para_editar_y_eliminar(): void
    {
        $proveedor = Proveedor::create(['nombre' => 'Proveedor institucional']);

        $this->get(route('licencia.proveedores'))
            ->assertOk()
            ->assertSee(route('licencia.proveedores.edit', $proveedor), false)
            ->assertSee(route('licencia.proveedores.destroy', $proveedor), false)
            ->assertSee('data-confirm-delete', false)
            ->assertSee('¿Eliminar proveedor?');
    }

    public function test_formulario_de_edicion_muestra_todos_los_datos(): void
    {
        $proveedor = Proveedor::create($this->datosProveedor());

        $this->get(route('licencia.proveedores.edit', $proveedor))
            ->assertOk()
            ->assertSee('Editar proveedor')
            ->assertSee('value="Proveedor actualizado"', false)
            ->assertSee('value="Proveedor Actualizado, S.A. de C.V."', false)
            ->assertSee('value="PAC010101ABC"', false)
            ->assertSee('value="Contacto Institucional"', false)
            ->assertSee('contacto@proveedor.test')
            ->assertSee('55 1234 5678')
            ->assertSee('https://proveedor.test')
            ->assertSee('Observaciones del proveedor');
    }

    public function test_proveedor_se_actualiza_y_puede_desactivarse(): void
    {
        $proveedor = Proveedor::create(['nombre' => 'Proveedor anterior', 'activo' => true]);

        $this->put(route('licencia.proveedores.update', $proveedor), [
            ...$this->datosProveedor(),
            'activo' => '0',
        ])->assertRedirect(route('licencia.proveedores'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Proveedor actualizado correctamente.');

        $proveedor->refresh();
        $this->assertSame('Proveedor actualizado', $proveedor->nombre);
        $this->assertFalse($proveedor->activo);
        $this->assertSame('PAC010101ABC', $proveedor->rfc);
    }

    public function test_actualizacion_invalida_conserva_el_registro(): void
    {
        $proveedor = Proveedor::create(['nombre' => 'Proveedor original']);

        $this->from(route('licencia.proveedores.edit', $proveedor))
            ->put(route('licencia.proveedores.update', $proveedor), ['nombre' => ''])
            ->assertRedirect(route('licencia.proveedores.edit', $proveedor))
            ->assertSessionHasErrors('nombre');

        $this->assertSame('Proveedor original', $proveedor->fresh()->nombre);
    }

    public function test_proveedor_se_elimina_y_la_licencia_se_conserva_sin_proveedor(): void
    {
        $proveedor = Proveedor::create(['nombre' => 'Proveedor para eliminar']);
        $licencia = Licencia::create([
            'clave' => 'LIC-PROVEEDOR',
            'nombre' => 'Licencia relacionada',
            'producto' => 'Producto',
            'tipo_licencia' => 'suscripcion',
            'cantidad_adquirida' => 1,
            'estado' => 'activa',
            'proveedor_id' => $proveedor->id,
            'fecha_vencimiento' => today()->addYear(),
        ]);

        $this->delete(route('licencia.proveedores.destroy', $proveedor))
            ->assertRedirect(route('licencia.proveedores'))
            ->assertSessionHas('success', 'Proveedor eliminado correctamente.');

        $this->assertDatabaseMissing('proveedores', ['id' => $proveedor->id]);
        $this->assertNull($licencia->fresh()->proveedor_id);
    }

    private function datosProveedor(): array
    {
        return [
            'nombre' => 'Proveedor actualizado',
            'razon_social' => 'Proveedor Actualizado, S.A. de C.V.',
            'rfc' => 'PAC010101ABC',
            'contacto_nombre' => 'Contacto Institucional',
            'contacto_email' => 'contacto@proveedor.test',
            'contacto_telefono' => '55 1234 5678',
            'sitio_web' => 'https://proveedor.test',
            'activo' => true,
            'observaciones' => 'Observaciones del proveedor',
        ];
    }
}
