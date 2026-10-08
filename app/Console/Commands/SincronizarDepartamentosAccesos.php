<?php

namespace App\Console\Commands;

use App\Services\SincronizarDepartamentosAccesos as Sincronizador;
use Illuminate\Console\Command;
use Throwable;

class SincronizarDepartamentosAccesos extends Command
{
    protected $signature = 'accesos:sincronizar-departamentos';

    protected $description = 'Sincroniza la réplica local de departamentos desde Accesos';

    public function handle(Sincronizador $sincronizador): int
    {
        try {
            $total = $sincronizador->ejecutar();
            $this->info("Se sincronizaron {$total} departamentos de Accesos.");

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);
            $this->error('No fue posible sincronizar los departamentos de Accesos.');

            return self::FAILURE;
        }
    }
}
