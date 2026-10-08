<?php

namespace App\Services;

use App\Models\DepartamentoAcceso;
use Composer\CaBundle\CaBundle;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SincronizarDepartamentosAccesos
{
    public function ejecutar(): int
    {
        $departamentos = $this->obtenerTodos();

        DB::transaction(function () use ($departamentos): void {
            foreach ($departamentos as $departamento) {
                if (! isset($departamento['id'], $departamento['nombre'])) {
                    continue;
                }

                DepartamentoAcceso::withTrashed()->updateOrCreate(
                    ['id' => $departamento['id']],
                    $this->atributos($departamento),
                );
            }
        });

        return count($departamentos);
    }

    private function obtenerTodos(): array
    {
        $primeraPagina = $this->consultarPagina(1);
        $departamentos = $primeraPagina['data'] ?? [];
        $ultimaPagina = (int) data_get($primeraPagina, 'meta.last_page', 1);

        for ($pagina = 2; $pagina <= $ultimaPagina; $pagina++) {
            $departamentos = [...$departamentos, ...($this->consultarPagina($pagina)['data'] ?? [])];
        }

        return $departamentos;
    }

    private function consultarPagina(int $pagina): array
    {
        return Http::acceptJson()
            ->withOptions(['verify' => CaBundle::getSystemCaRootBundlePath()])
            ->timeout(10)
            ->get(config('services.accesos.departamentos_url'), ['page' => $pagina])
            ->throw()
            ->json();
    }

    private function atributos(array $departamento): array
    {
        return [
            'nombre' => $departamento['nombre'],
            'siglas' => $departamento['siglas'] ?? null,
            'descripcion' => $departamento['descripcion'] ?? null,
            'parent_id' => $departamento['parent_id'] ?? null,
            'activo' => $departamento['activo'] ?? true,
            'created_at' => $this->fecha($departamento['created_at'] ?? null),
            'updated_at' => $this->fecha($departamento['updated_at'] ?? null),
            'deleted_at' => $this->fecha($departamento['deleted_at'] ?? null),
        ];
    }

    private function fecha(mixed $fecha): ?Carbon
    {
        return $fecha ? Carbon::parse($fecha) : null;
    }
}
