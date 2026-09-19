<?php

namespace App\Services;

use App\Models\Asignacion;
use App\Models\Bien;
use App\Models\Responsable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class RegistrarBien
{
    public function registrar(array $data, ?UploadedFile $fotografia, ?string $ip): Bien
    {
        $path = $fotografia?->store('bienes', 'local');
        if ($fotografia && ! $path) {
            throw new RuntimeException('No se pudo guardar la fotografía.');
        }

        $qrPath = null;
        try {
            return DB::transaction(function () use ($data, $path, $ip, &$qrPath) {
                $anio = now()->year;
                DB::table('consecutivos')->insertOrIgnore([
                    'tipo' => 'BIEN', 'anio' => $anio, 'ultimo_numero' => 0,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                $query = DB::table('consecutivos')->where('tipo', 'BIEN')->where('anio', $anio);
                $numero = $query->lockForUpdate()->first()->ultimo_numero;
                do {
                    $numero++;
                    $suffix = $anio.'-'.str_pad((string) $numero, 6, '0', STR_PAD_LEFT);
                } while (Bien::withTrashed()->where('folio_sicam', 'SICAM-'.$suffix)->orWhere('numero_patrimonial', 'PAT-'.$suffix)->exists());
                $query->update(['ultimo_numero' => $numero, 'updated_at' => now()]);

                $bien = Bien::create([
                    ...Arr::except($data, ['responsabilidad', 'responsable_id', 'crear_responsable', 'nuevo_responsable', 'campos', 'fotografia']),
                    'folio_sicam' => 'SICAM-'.$suffix,
                    'numero_patrimonial' => 'PAT-'.$suffix,
                    'fotografia_path' => $path,
                ]);
                foreach ($data['campos'] ?? [] as $campoId => $valor) {
                    \App\Models\ValorBien::create([
                        'bien_id' => $bien->id, 'campo_categoria_id' => $campoId, 'valor' => $valor,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
                $asignacion = null;
                if ($data['responsabilidad'] !== 'ninguna') {
                    $responsableId = $data['responsable_id'] ?? null;
                    if ($data['responsabilidad'] === 'persona' && ! empty($data['crear_responsable'])) {
                        $responsableId = Responsable::create([
                            ...$data['nuevo_responsable'],
                            'dependencia_id_accesos' => $bien->dependencia_id_accesos,
                            'area_id_accesos' => $bien->area_id_accesos,
                        ])->id;
                    }
                    $asignacion = Asignacion::create([
                        'bien_id' => $bien->id, 'tipo_responsabilidad' => strtoupper($data['responsabilidad']),
                        'responsable_id' => $responsableId,
                        'dependencia_id_accesos' => $bien->dependencia_id_accesos,
                        'area_id_accesos' => $bien->area_id_accesos,
                        'fecha_inicio' => now(),
                    ]);
                }
                \App\Models\MovimientoBien::create([
                    'bien_id' => $bien->id, 'asignacion_id' => $asignacion?->id, 'tipo' => 'ALTA',
                    'valor_nuevo' => $bien->getAttributes(),
                    'ip_origen' => $ip, 'created_at' => now(), 'updated_at' => now(),
                ]);

                $qrPath = 'qr/'.$bien->public_id.'.svg';
                app(QrBien::class)->guardar($bien);

                return $bien;
            });
        } catch (Throwable $exception) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            if ($qrPath) {
                Storage::disk('local')->delete($qrPath);
            }
            throw $exception;
        }
    }
}
