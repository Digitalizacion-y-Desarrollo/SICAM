<?php

namespace App\Services;

use App\Models\Auditoria;
use App\Models\Responsable;
use App\Models\Sistema;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ObtenerBitacoraSistema
{
    private const CAMPOS = [
        'clave' => 'Clave',
        'nombre' => 'Nombre',
        'descripcion' => 'Descripción',
        'objetivo' => 'Objetivo',
        'tipo' => 'Tipo',
        'origen' => 'Origen',
        'estado' => 'Estado',
        'dependencia_id_accesos' => 'Dependencia',
        'area_id_accesos' => 'Área',
        'responsable_funcional_id' => 'Responsable funcional',
        'responsable_tecnico_id' => 'Responsable técnico',
        'url_produccion' => 'URL de producción',
        'repositorio_url' => 'Repositorio',
        'fecha_inicio' => 'Fecha de inicio',
        'fecha_liberacion' => 'Fecha de liberación',
    ];

    public function ejecutar(Sistema $sistema, int $limiteOperaciones = 10): Collection
    {
        $auditorias = Auditoria::query()
            ->where('entidad', $sistema->getTable())
            ->where('entidad_id', $sistema->getKey())
            ->latest('created_at')
            ->latest('id')
            ->limit($limiteOperaciones)
            ->get();

        $responsables = $this->responsablesDe($auditorias);

        return $auditorias->flatMap(function (Auditoria $auditoria) use ($responsables) {
            $anteriores = $auditoria->valores_anteriores ?? [];
            $nuevos = $auditoria->valores_nuevos ?? [];
            $campos = array_unique([...array_keys($anteriores), ...array_keys($nuevos)]);

            return collect($campos)
                ->filter(fn (string $campo) => isset(self::CAMPOS[$campo]))
                ->map(fn (string $campo) => (object) [
                    'created_at' => $auditoria->created_at,
                    'accion' => match ($auditoria->accion) {
                        'CREAR' => 'Creación',
                        'ACTUALIZAR' => 'Actualización',
                        'ELIMINAR' => 'Eliminación',
                        default => ucfirst(mb_strtolower($auditoria->accion)),
                    },
                    'campo' => self::CAMPOS[$campo],
                    'valor_anterior' => $this->formatearValor($campo, $anteriores[$campo] ?? null, $responsables),
                    'valor_nuevo' => $this->formatearValor($campo, $nuevos[$campo] ?? null, $responsables),
                    'usuario_nombre' => $auditoria->usuario_nombre ?: match ($auditoria->origen) {
                        'CONSOLA' => 'Proceso de consola',
                        'SIN_SESION' => 'Sin sesión',
                        default => 'Sistema',
                    },
                ]);
        })->values();
    }

    private function responsablesDe(Collection $auditorias): Collection
    {
        $ids = $auditorias->flatMap(function (Auditoria $auditoria) {
            return collect([$auditoria->valores_anteriores, $auditoria->valores_nuevos])
                ->filter()
                ->flatMap(fn (array $valores) => [
                    $valores['responsable_funcional_id'] ?? null,
                    $valores['responsable_tecnico_id'] ?? null,
                ]);
        })->filter()->unique()->values();

        return Responsable::withTrashed()->whereIn('id', $ids)->get()->keyBy('id');
    }

    private function formatearValor(string $campo, mixed $valor, Collection $responsables): string
    {
        if ($valor === null || $valor === '') {
            return '—';
        }

        if (in_array($campo, ['responsable_funcional_id', 'responsable_tecnico_id'], true)) {
            return $responsables->get($valor)?->nombre_completo ?? "Responsable #{$valor}";
        }

        if ($campo === 'tipo') {
            return Sistema::TIPOS[$valor] ?? (string) $valor;
        }

        if ($campo === 'origen') {
            return Sistema::ORIGENES[$valor] ?? (string) $valor;
        }

        if ($campo === 'estado') {
            return Sistema::ESTADOS[$valor] ?? (string) $valor;
        }

        if (in_array($campo, ['fecha_inicio', 'fecha_liberacion'], true)) {
            return Carbon::parse($valor)->format('d/m/Y');
        }

        if (is_bool($valor)) {
            return $valor ? 'Sí' : 'No';
        }

        if (is_array($valor)) {
            return json_encode($valor, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return (string) $valor;
    }
}
