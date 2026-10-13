<?php

namespace App\Support;

use App\Models\DepartamentoAcceso;
use Illuminate\Database\Eloquent\Builder;

final class AlcanceAreaUsuario
{
    public static function aplicar(Builder $query): void
    {
        self::aplicarDepartamento($query);
    }

    public static function aplicarDepartamento(Builder $query): void
    {
        if (self::esSuperusuario() || ! session()->has('accesos.departamento')) {
            return;
        }

        $dependencia = self::ubicacion()['dependencia'];

        if (filled($dependencia)) {
            $query->where($query->qualifyColumn('dependencia_id_accesos'), $dependencia);

            return;
        }

        $query->whereRaw('1 = 0');
    }

    public static function aplicarPorBien(Builder $query): void
    {
        if (self::esSuperusuario() || ! session()->has('accesos.departamento')) {
            return;
        }

        $dependencia = self::ubicacion()['dependencia'];

        if (filled($dependencia)) {
            $query->whereHas('bien', fn (Builder $bien) => $bien->where('dependencia_id_accesos', $dependencia));

            return;
        }

        $query->whereRaw('1 = 0');
    }

    public static function esSuperusuario(): bool
    {
        $roles = session('accesos.roles', []);

        return is_array($roles) && in_array('superuser', $roles, true);
    }

    public static function ubicacion(): array
    {
        $departamento = session('accesos.departamento', []);

        $dependenciaId = data_get($departamento, 'departamento_padre.id');
        $areaId = data_get($departamento, 'departamento_hijo.id');
        $ids = collect([$dependenciaId, $areaId])
            ->filter(fn ($id) => filled($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return ['dependencia' => null, 'area' => null];
        }

        $departamentos = DepartamentoAcceso::query()
            ->where('activo', true)
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        $dependencia = filled($dependenciaId) ? $departamentos->get((int) $dependenciaId) : null;
        $area = filled($areaId) ? $departamentos->get((int) $areaId) : null;

        // Algunos perfiles de Accesos sólo incluyen el área. En ese caso el
        // alcance sigue siendo la dependencia padre de dicha área.
        if (! $dependencia && $area?->parent_id) {
            $dependencia = DepartamentoAcceso::query()
                ->whereKey($area->parent_id)
                ->where('activo', true)
                ->first();
        }

        if ($dependencia && $area && $area->parent_id !== $dependencia->id) {
            $area = null;
        }

        return [
            'dependencia' => $dependencia?->id,
            'area' => $area?->id,
        ];
    }
}
