<?php

namespace App\Services;

use App\Models\Sistema;
use Illuminate\Support\Carbon;

class RegistrarEstadoSistema
{
    public function inicial(Sistema $sistema, ?string $fecha = null, ?string $observaciones = null): void
    {
        $sistema->historialEstados()->create([
            'estado' => $sistema->estado,
            'fecha_inicio' => $fecha ?: $sistema->fecha_inicio?->toDateString() ?: today()->toDateString(),
            'observaciones' => $observaciones,
        ]);
    }

    public function cambio(Sistema $sistema, string $estadoAnterior, ?string $fecha = null, ?string $observaciones = null): void
    {
        if ($estadoAnterior === $sistema->estado) {
            return;
        }

        $inicio = Carbon::parse($fecha ?: today())->startOfDay();
        $vigente = $sistema->historialEstados()->whereNull('fecha_fin')->latest('fecha_inicio')->first();

        if ($vigente) {
            $vigente->update(['fecha_fin' => $inicio->toDateString()]);
        }

        $sistema->historialEstados()->create([
            'estado' => $sistema->estado,
            'fecha_inicio' => $inicio->toDateString(),
            'observaciones' => $observaciones,
        ]);
    }
}
