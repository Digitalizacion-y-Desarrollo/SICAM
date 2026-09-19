<?php

namespace App\Services;

use Composer\CaBundle\CaBundle;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class DepartamentosAccesos
{
    public function listar(): array
    {
        if (app()->runningUnitTests()) {
            return [];
        }

        return Cache::remember('accesos.departamentos', now()->addHours(6), function (): array {
            try {
                $primeraPagina = $this->consultarPagina(1);
                $departamentos = $primeraPagina['data'] ?? [];
                $ultimaPagina = (int) data_get($primeraPagina, 'meta.last_page', 1);

                for ($pagina = 2; $pagina <= $ultimaPagina; $pagina++) {
                    $departamentos = [...$departamentos, ...($this->consultarPagina($pagina)['data'] ?? [])];
                }

                return collect($departamentos)
                    ->filter(fn ($departamento) => ($departamento['activo'] ?? false) && isset($departamento['id'], $departamento['nombre']))
                    ->map(fn ($departamento) => [
                        'id' => (string) $departamento['id'],
                        'nombre' => $departamento['nombre'],
                        'parent_id' => isset($departamento['parent_id']) ? (string) $departamento['parent_id'] : null,
                    ])
                    ->sortBy('nombre', SORT_NATURAL | SORT_FLAG_CASE)
                    ->values()
                    ->all();
            } catch (Throwable $exception) {
                Log::warning('No se pudieron consultar los departamentos de Accesos.', [
                    'exception' => $exception->getMessage(),
                ]);

                return [];
            }
        });
    }

    private function consultarPagina(int $pagina): array
    {
        return Http::acceptJson()
            ->withOptions(['verify' => CaBundle::getSystemCaRootBundlePath()])
            ->timeout(5)
            ->get(config('services.accesos.departamentos_url'), ['page' => $pagina])
            ->throw()
            ->json();
    }
}
