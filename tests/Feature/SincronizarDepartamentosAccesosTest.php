<?php

namespace Tests\Feature;

use App\Models\DepartamentoAcceso;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SincronizarDepartamentosAccesosTest extends TestCase
{
    use RefreshDatabase;

    public function test_sincroniza_todos_los_campos_y_paginas_de_accesos(): void
    {
        config()->set('services.accesos.departamentos_url', 'https://accesos.test/api/departamentos');

        Http::fake([
            'https://accesos.test/api/departamentos?page=1' => Http::response([
                'data' => [[
                    'id' => 10, 'nombre' => 'Administración', 'siglas' => 'ADM',
                    'descripcion' => 'Área administrativa', 'parent_id' => null, 'activo' => true,
                    'created_at' => '2026-01-01 08:00:00', 'updated_at' => '2026-02-01 09:00:00', 'deleted_at' => null,
                ]],
                'meta' => ['last_page' => 2],
            ]),
            'https://accesos.test/api/departamentos?page=2' => Http::response([
                'data' => [[
                    'id' => 11, 'nombre' => 'Sistemas', 'siglas' => null,
                    'descripcion' => null, 'parent_id' => 10, 'activo' => false,
                    'created_at' => null, 'updated_at' => null, 'deleted_at' => '2026-03-01 10:00:00',
                ]],
                'meta' => ['last_page' => 2],
            ]),
        ]);

        $this->artisan('accesos:sincronizar-departamentos')->assertSuccessful();

        $this->assertDatabaseHas('departamentos_accesos', [
            'id' => 10, 'nombre' => 'Administración', 'siglas' => 'ADM', 'parent_id' => null, 'activo' => true,
        ]);
        $this->assertTrue(DepartamentoAcceso::withTrashed()->findOrFail(11)->trashed());
        Http::assertSentCount(2);
    }
}
