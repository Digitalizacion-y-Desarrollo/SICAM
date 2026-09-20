<?php

namespace App\Http\Controllers;

use App\Http\Requests\LicenciaRequest;
use App\Models\Licencia;
use App\Models\Proveedor;
use App\Models\Responsable;
use App\Services\DepartamentosAccesos;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LicenciaController extends Controller
{
    public function resumen(): View
    {
        $conteosEstado = Licencia::query()
            ->selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');
        $total = $conteosEstado->sum();

        $estadoResumen = collect(Licencia::ESTADOS)->map(fn ($etiqueta, $estado) => [
            'estado' => $estado,
            'etiqueta' => $etiqueta,
            'total' => $conteosEstado->get($estado, 0),
            'porcentaje' => $total ? (int) round(($conteosEstado->get($estado, 0) / $total) * 100) : 0,
        ])->values();

        $porTipo = Licencia::query()
            ->selectRaw('tipo_licencia, COUNT(*) as total')
            ->groupBy('tipo_licencia')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        $proximosVencimientos = Licencia::query()
            ->with('proveedor')
            ->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '>=', today())
            ->whereNotIn('estado', ['cancelada', 'vencida'])
            ->orderBy('fecha_vencimiento')
            ->take(5)
            ->get();

        $metricas = [
            'total' => $total,
            'activas' => $conteosEstado->get('activa', 0),
            'por_vencer' => $conteosEstado->get('por_vencer', 0),
            'vencidas' => $conteosEstado->get('vencida', 0),
            'cantidad_adquirida' => Licencia::query()->sum('cantidad_adquirida'),
        ];

        return view('software.licencias.resumen', compact(
            'metricas',
            'estadoResumen',
            'porTipo',
            'proximosVencimientos'
        ));
    }

    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'buscar' => ['nullable', 'string', 'max:191'],
            'estado' => ['nullable', Rule::in(array_keys(Licencia::ESTADOS))],
            'tipo' => ['nullable', Rule::in(array_keys(Licencia::TIPOS))],
        ]);

        $licencias = Licencia::query()
            ->with(['proveedor', 'responsable'])
            ->when($filtros['buscar'] ?? null, fn ($query, $buscar) => $query->where(
                fn ($query) => $query
                    ->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('clave', 'like', "%{$buscar}%")
                    ->orWhere('producto', 'like', "%{$buscar}%")
                    ->orWhere('fabricante', 'like', "%{$buscar}%")
            ))
            ->when($filtros['estado'] ?? null, fn ($query, $estado) => $query->where('estado', $estado))
            ->when($filtros['tipo'] ?? null, fn ($query, $tipo) => $query->where('tipo_licencia', $tipo))
            ->orderBy('nombre')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        $conteos = Licencia::query()
            ->selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        return view('software.licencias.index', compact('licencias', 'conteos', 'filtros'));
    }

    public function create(DepartamentosAccesos $departamentosAccesos): View
    {
        $proveedores = Proveedor::query()->where('activo', true)->orderBy('nombre')->get();
        $responsables = Responsable::query()->where('activo', true)->orderBy('nombre')->get();
        $departamentos = $departamentosAccesos->listar();
        $clave = Licencia::siguienteClave();

        return view('software.licencias.create', compact('proveedores', 'responsables', 'departamentos', 'clave'));
    }

    public function store(LicenciaRequest $request)
    {
        $data = $request->validated();

        dd($data);
    }
}
