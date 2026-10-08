<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

final class AlcanceAreaUsuario
{
    public static function aplicar(Builder $query): void
    {
        if (self::esSuperusuario() || ! session()->has('accesos.departamento')) {
            return;
        }

        $area = self::ubicacion()['area'];

        if (filled($area)) {
            $query->where($query->qualifyColumn('area_id_accesos'), $area);

            return;
        }

        $query->whereRaw('1 = 0');
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

        return [
            'dependencia' => data_get($departamento, 'departamento_padre.nombre'),
            'area' => data_get($departamento, 'departamento_hijo.nombre'),
        ];
    }
}
