<?php

namespace App\Services;

use App\Models\Bien;
use App\Models\MovimientoBien;
use App\Models\Responsable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class ActualizarBien
{
    public function actualizar(Bien $bien, array $data, ?UploadedFile $foto): void
    {
        $path = $foto?->store('bienes', 'local');
        if ($foto && ! $path) throw new RuntimeException('No se pudo guardar la fotografía.');
        $oldPhoto = $bien->fotografia_path;
        try {
            DB::transaction(function () use ($bien, $data, $path) {
                $bien = Bien::whereKey($bien->id)->lockForUpdate()->firstOrFail();
                $antes = $bien->getAttributes();
                $actual = $bien->asignaciones()->whereNull('fecha_fin')->first();
                $cambioResponsable = ($data['responsabilidad'] !== strtolower($actual?->tipo_responsabilidad ?? 'ninguna'))
                    || (string) ($data['responsable_id'] ?? '') !== (string) ($actual?->responsable_id ?? '')
                    || $data['dependencia_id_accesos'] !== $bien->dependencia_id_accesos || $data['area_id_accesos'] !== $bien->area_id_accesos
                    || ! empty($data['crear_responsable']);
                if (! empty($data['crear_responsable']) && $data['responsabilidad'] === 'persona') {
                    $data['responsable_id'] = Responsable::create([
                        ...$data['nuevo_responsable'], 'dependencia_id_accesos' => $data['dependencia_id_accesos'], 'area_id_accesos' => $data['area_id_accesos'],
                    ])->id;
                }
                if ($cambioResponsable) app(AsignarBien::class)->asignar($bien, $data);
                $bien->refresh();
                $bien->fill(Arr::except($data, ['campos', 'fotografia', 'responsabilidad', 'responsable_id', 'nuevo_responsable', 'crear_responsable']));
                if ($path) $bien->fotografia_path = $path;
                $bien->save();
                foreach ($bien->valores()->get() as $valor) {
                    if (! array_key_exists($valor->campo_categoria_id, $data['campos'] ?? [])) $valor->delete();
                }
                foreach ($data['campos'] ?? [] as $campoId => $valor) {
                    $bien->valores()->updateOrCreate(['campo_categoria_id' => $campoId], ['valor' => $valor]);
                }
                MovimientoBien::create(['bien_id' => $bien->id, 'tipo' => 'ACTUALIZACION', 'valor_anterior' => $antes, 'valor_nuevo' => $bien->getAttributes(), 'ip_origen' => request()->ip()]);
            });
        } catch (Throwable $e) {
            if ($path) Storage::disk('local')->delete($path);
            throw $e;
        }
        if ($path && $oldPhoto) Storage::disk('local')->delete($oldPhoto);
    }

    public function eliminar(Bien $bien): void
    {
        DB::transaction(function () use ($bien) {
            $bien = Bien::whereKey($bien->id)->lockForUpdate()->firstOrFail();
            foreach ($bien->asignaciones()->whereNull('fecha_fin')->get() as $asignacion) $asignacion->update(['fecha_fin' => now()]);
            MovimientoBien::create(['bien_id' => $bien->id, 'tipo' => 'BAJA', 'valor_anterior' => $bien->getAttributes(), 'ip_origen' => request()->ip()]);
            $bien->delete();
        });
    }
}
