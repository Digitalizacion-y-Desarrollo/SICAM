<?php

namespace App\Http\Controllers;

use App\Models\Bien;
use App\Models\Importacion;
use App\Models\Licencia;
use App\Models\MovimientoBien;
use App\Models\Responsable;
use App\Models\Sistema;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $conteos = [
            'bienes' => Gate::allows('bienes.ver') ? Bien::query()->count() : 0,
            'sistemas' => Gate::allows('sistemas.ver') ? Sistema::query()->count() : 0,
            'licencias' => Gate::allows('licencias.ver') ? Licencia::query()->count() : 0,
            'unidades_licencia' => Gate::allows('licencias.ver') ? (int) Licencia::query()->sum('cantidad_adquirida') : 0,
            'responsables' => Gate::allows('responsables.ver') ? Responsable::query()->where('activo', true)->count() : 0,
        ];

        $dependencias = $this->valoresAdministrativos('dependencia_id_accesos');
        $areas = $this->valoresAdministrativos('area_id_accesos');

        $alertas = [
            'bienes_sin_resguardo' => Gate::allows('bienes.ver') ? Bien::query()
                ->whereDoesntHave('asignaciones', fn ($query) => $query->whereNull('fecha_fin'))
                ->count() : 0,
            'licencias_por_vencer' => Gate::allows('licencias.ver') ? Licencia::query()
                ->whereNotIn('estado', ['cancelada', 'vencida'])
                ->whereBetween('fecha_vencimiento', [today(), today()->addDays(30)])
                ->count() : 0,
            'importaciones_con_errores' => Gate::allows('importaciones.ver') ? Importacion::query()
                ->get(['errores'])
                ->filter(fn (Importacion $importacion) => filled($importacion->errores))
                ->count() : 0,
        ];

        $metricas = [
            'dependencias' => $dependencias->count(),
            'areas' => $areas->count(),
            'registros' => $conteos['bienes'] + $conteos['sistemas'] + $conteos['licencias'],
            'alertas' => array_sum($alertas),
        ];

        return view('dashboard.index', [
            'conteos' => $conteos,
            'metricas' => $metricas,
            'alertas' => $alertas,
            'actividad' => $this->actividadReciente(),
            'actualizadoEn' => now(),
        ]);
    }

    private function valoresAdministrativos(string $campo): Collection
    {
        return collect()
            ->when(Gate::allows('bienes.ver'), fn (Collection $valores) => $valores->merge(Bien::query()->whereNotNull($campo)->pluck($campo)))
            ->when(Gate::allows('sistemas.ver'), fn (Collection $valores) => $valores->merge(Sistema::query()->whereNotNull($campo)->pluck($campo)))
            ->when(Gate::allows('licencias.ver'), fn (Collection $valores) => $valores->merge(Licencia::query()->whereNotNull($campo)->pluck($campo)))
            ->when(Gate::allows('responsables.ver'), fn (Collection $valores) => $valores->merge(Responsable::query()->whereNotNull($campo)->pluck($campo)))
            ->filter()
            ->unique()
            ->values();
    }

    private function actividadReciente(): Collection
    {
        $movimientos = Gate::allows('movimientos.ver') ? MovimientoBien::query()
            ->with('bien')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn (MovimientoBien $movimiento) => [
                'modulo' => 'PATRIMONIO',
                'descripcion' => ucfirst(strtolower(str_replace('_', ' ', $movimiento->tipo))).': '.($movimiento->bien?->nombre ?? 'Bien eliminado'),
                'detalle' => $movimiento->bien?->folio_sicam,
                'fecha' => $movimiento->created_at,
                'tono' => 'brand',
                'url' => route('patrimonio.movimientos.show', $movimiento),
            ]) : collect();

        $sistemas = Gate::allows('sistemas.ver') ? Sistema::query()
            ->latest('updated_at')
            ->take(4)
            ->get()
            ->map(fn (Sistema $sistema) => [
                'modulo' => 'SISTEMAS',
                'descripcion' => 'Actualización de sistema: '.$sistema->nombre,
                'detalle' => $sistema->clave,
                'fecha' => $sistema->updated_at,
                'tono' => 'gold',
                'url' => route('software.sistemas.show', $sistema),
            ]) : collect();

        $licencias = Gate::allows('licencias.ver') ? Licencia::query()
            ->latest('updated_at')
            ->take(4)
            ->get()
            ->map(fn (Licencia $licencia) => [
                'modulo' => 'LICENCIAS',
                'descripcion' => 'Actualización de licencia: '.$licencia->nombre,
                'detalle' => $licencia->clave,
                'fecha' => $licencia->updated_at,
                'tono' => 'brand',
                'url' => route('licencia.index', ['buscar' => $licencia->clave]),
            ]) : collect();

        return $movimientos
            ->concat($sistemas)
            ->concat($licencias)
            ->sortByDesc('fecha')
            ->take(6)
            ->values();
    }
}
