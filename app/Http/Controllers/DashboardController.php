<?php

namespace App\Http\Controllers;

use App\Models\Bien;
use App\Models\Importacion;
use App\Models\Licencia;
use App\Models\MovimientoBien;
use App\Models\Responsable;
use App\Models\Sistema;
use App\Support\AlcanceAreaUsuario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $conteos = [
            'bienes' => Gate::allows('bienes.ver') ? $this->porDepartamento(Bien::query())->count() : 0,
            'sistemas' => Gate::allows('sistemas.ver') ? $this->porDepartamento(Sistema::query())->count() : 0,
            'licencias' => Gate::allows('licencias.ver') ? $this->porDepartamento(Licencia::query())->count() : 0,
            'unidades_licencia' => Gate::allows('licencias.ver') ? (int) $this->porDepartamento(Licencia::query())->sum('cantidad_adquirida') : 0,
            'responsables' => Gate::allows('responsables.ver') ? $this->porDepartamento(Responsable::query())->where('activo', true)->count() : 0,
        ];

        $dependencias = $this->valoresAdministrativos('dependencia_id_accesos');
        $areas = $this->valoresAdministrativos('area_id_accesos');

        $alertas = [
            'bienes_sin_resguardo' => Gate::allows('bienes.ver') ? $this->porDepartamento(Bien::query())
                ->whereDoesntHave('asignaciones', fn ($query) => $query->whereNull('fecha_fin'))
                ->count() : 0,
            'licencias_por_vencer' => Gate::allows('licencias.ver') ? $this->porDepartamento(Licencia::query())
                ->whereNotIn('estado', ['cancelada', 'vencida'])
                ->whereBetween('fecha_vencimiento', [today(), today()->addDays(30)])
                ->count() : 0,
            'importaciones_con_errores' => Gate::allows('importaciones.ver') ? $this->importacionesDelDepartamento()
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
            ->when(Gate::allows('bienes.ver'), fn (Collection $valores) => $valores->merge($this->porDepartamento(Bien::query())->whereNotNull($campo)->pluck($campo)))
            ->when(Gate::allows('sistemas.ver'), fn (Collection $valores) => $valores->merge($this->porDepartamento(Sistema::query())->whereNotNull($campo)->pluck($campo)))
            ->when(Gate::allows('licencias.ver'), fn (Collection $valores) => $valores->merge($this->porDepartamento(Licencia::query())->whereNotNull($campo)->pluck($campo)))
            ->when(Gate::allows('responsables.ver'), fn (Collection $valores) => $valores->merge($this->porDepartamento(Responsable::query())->whereNotNull($campo)->pluck($campo)))
            ->filter()
            ->unique()
            ->values();
    }

    private function actividadReciente(): Collection
    {
        $movimientos = Gate::allows('movimientos.ver') ? $this->movimientosDelDepartamento()
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

        $sistemas = Gate::allows('sistemas.ver') ? $this->porDepartamento(Sistema::query())
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

        $licencias = Gate::allows('licencias.ver') ? $this->porDepartamento(Licencia::query())
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

    private function porDepartamento(Builder $query): Builder
    {
        AlcanceAreaUsuario::aplicarDepartamento($query);

        return $query;
    }

    private function movimientosDelDepartamento(): Builder
    {
        $query = MovimientoBien::query();
        AlcanceAreaUsuario::aplicarPorBien($query);

        return $query;
    }

    private function importacionesDelDepartamento(): Builder
    {
        $query = Importacion::query();

        if (! AlcanceAreaUsuario::esSuperusuario() && session()->has('accesos.departamento')) {
            $dependencia = AlcanceAreaUsuario::ubicacion()['dependencia'];
            $query->whereHas('bienes', fn (Builder $bien) => $bien->where('dependencia_id_accesos', $dependencia ?: '__sin_departamento__'));
        }

        return $query;
    }
}
