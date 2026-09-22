<?php

namespace App\Support;

final class PermisosAccesos
{
    public static function permite(string $permiso): bool
    {
        $roles = session('accesos.roles', []);
        $permisos = session('accesos.permissions', []);

        if (! is_array($roles) || ! is_array($permisos)) {
            return false;
        }

        $rolesTotales = config('permisos.roles_totales', []);

        return count(array_intersect($roles, $rolesTotales)) > 0
            || in_array('*', $permisos, true)
            || in_array($permiso, $permisos, true);
    }

    public static function permisos(): array
    {
        return collect(config('permisos.grupos', []))
            ->flatMap(fn (array $acciones, string $recurso) => collect($acciones)
                ->map(fn (string $accion) => $recurso.'.'.$accion))
            ->values()
            ->all();
    }

    public static function rutaInicial(): string
    {
        $rutas = [
            'dashboard-sicam.ver' => 'dashboard',
            'patrimonio.ver' => 'patrimonio.resumen',
            'bienes.ver' => 'patrimonio.bienes',
            'asignaciones.ver' => 'patrimonio.asignaciones.index',
            'movimientos.ver' => 'patrimonio.movimientos.index',
            'categorias.ver' => 'patrimonio.categorias',
            'importaciones.ver' => 'patrimonio.importaciones.index',
            'responsables.ver' => 'patrimonio.responsables',
            'auditoria.ver' => 'patrimonio.auditoria',
            'software.ver' => 'software.resumen',
            'sistemas.ver' => 'software.sistemas',
            'licencias.ver' => 'licencia.index',
            'proveedores.ver' => 'licencia.proveedores',
        ];

        foreach ($rutas as $permiso => $ruta) {
            if (self::permite($permiso)) {
                return $ruta;
            }
        }

        return 'sin-permisos';
    }
}
