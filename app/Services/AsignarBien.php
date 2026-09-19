<?php

namespace App\Services;

use App\Models\Asignacion;
use App\Models\Bien;
use App\Models\MovimientoBien;
use App\Models\Responsable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AsignarBien
{
    public function asignar(Bien $bien, array $data): ?Asignacion
    {
        return DB::transaction(function () use ($bien, $data) {
            $bien = Bien::whereKey($bien->id)->lockForUpdate()->firstOrFail();
            $actual = $bien->asignaciones()->whereNull('fecha_fin')->lockForUpdate()->first();
            $responsableId = $data['responsable_id'] ?? null;
            $area = $data['area_id_accesos'] ?? null;
            if ($data['responsabilidad'] === 'persona') {
                $responsable = Responsable::whereKey($responsableId)->where('activo', true)->lockForUpdate()->first();
                if (! $responsable || $responsable->dependencia_id_accesos !== $data['dependencia_id_accesos'] || $responsable->area_id_accesos !== $area) {
                    throw ValidationException::withMessages(['responsable_id' => 'El responsable no está activo o no pertenece a la dependencia y área indicadas.']);
                }
            }
            if ($actual) {
                $actual->update(['fecha_fin' => now()]);
            }
            $nueva = $data['responsabilidad'] === 'ninguna' ? null : Asignacion::create([
                'bien_id' => $bien->id, 'tipo_responsabilidad' => strtoupper($data['responsabilidad']),
                'responsable_id' => $data['responsabilidad'] === 'persona' ? $responsableId : null,
                'dependencia_id_accesos' => $data['dependencia_id_accesos'], 'area_id_accesos' => $area,
                'observaciones' => $data['observaciones'] ?? null, 'fecha_inicio' => now(),
            ]);
            $anterior = $bien->only(['estado', 'dependencia_id_accesos', 'area_id_accesos']);
            $bien->update([
                'dependencia_id_accesos' => $data['dependencia_id_accesos'], 'area_id_accesos' => $area,
                'estado' => $nueva ? ($data['estado'] ?? 'ASIGNADO') : 'DISPONIBLE',
            ]);
            MovimientoBien::create([
                'bien_id' => $bien->id, 'asignacion_id' => $nueva?->id ?? $actual?->id,
                'tipo' => $nueva ? ($actual ? 'REASIGNACION' : 'ASIGNACION') : 'FIN_ASIGNACION',
                'valor_anterior' => $anterior, 'valor_nuevo' => $bien->only(array_keys($anterior)),
                'observaciones' => $data['observaciones'] ?? null, 'ip_origen' => request()->ip(),
            ]);

            return $nueva;
        });
    }
}
