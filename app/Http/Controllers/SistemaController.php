<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarSistemaRequest;
use App\Http\Requests\sistemaRequest;
use App\Models\Auditoria;
use App\Models\Categoria;
use App\Models\Responsable;
use App\Models\Sistema;
use App\Services\DepartamentosAccesos;
use App\Services\GuardarSistema;
use App\Services\ObtenerBitacoraSistema;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SistemaController extends Controller
{
    public function resumen(): View
    {
        $conteosEstado = Sistema::query()
            ->selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');
        $total = $conteosEstado->sum();

        $estadoResumen = collect(Sistema::ESTADOS)->map(fn ($etiqueta, $estado) => [
            'estado' => $estado,
            'etiqueta' => $etiqueta,
            'total' => $conteosEstado->get($estado, 0),
            'porcentaje' => $total ? (int) round(($conteosEstado->get($estado, 0) / $total) * 100) : 0,
        ])->values();
        $recientes = Sistema::query()
            ->with(['responsableFuncional', 'responsableTecnico'])
            ->latest('updated_at')
            ->take(5)
            ->get();
        $porDependencia = Sistema::query()
            ->selectRaw('dependencia_id_accesos, COUNT(*) as total')
            ->groupBy('dependencia_id_accesos')
            ->orderByDesc('total')
            ->orderBy('dependencia_id_accesos')
            ->take(5)
            ->get();

        $metricas = [
            'total' => $total,
            'produccion' => $conteosEstado->get('produccion', 0),
            'en_proceso' => $conteosEstado->get('desarrollo', 0) + $conteosEstado->get('pruebas', 0),
            'sin_responsable' => Sistema::query()
                ->where(fn ($query) => $query->whereNull('responsable_funcional_id')->orWhereNull('responsable_tecnico_id'))
                ->count(),
        ];

        return view('software.resumen', compact('metricas', 'estadoResumen', 'recientes', 'porDependencia'));
    }

    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'buscar' => ['nullable', 'string', 'max:191'],
            'estado' => ['nullable', Rule::in(array_keys(Sistema::ESTADOS))],
            'tipo' => ['nullable', Rule::in(array_keys(Sistema::TIPOS))],
            'dependencia' => ['nullable', 'string', 'max:100'],
        ]);
        $sistemas = Sistema::query()->with(['responsableFuncional', 'responsableTecnico'])
            ->when($filtros['buscar'] ?? null, fn($q, $v) => $q->where(fn($q) => $q->where('nombre', 'like', "%{$v}%")->orWhere('clave', 'like', "%{$v}%")))
            ->when($filtros['estado'] ?? null, fn($q, $v) => $q->where('estado', $v))
            ->when($filtros['tipo'] ?? null, fn($q, $v) => $q->where('tipo', $v))
            ->when($filtros['dependencia'] ?? null, fn($q, $v) => $q->where('dependencia_id_accesos', $v))
            ->orderBy('nombre')->orderBy('id')->paginate(15)->withQueryString();
        $dependencias = Sistema::query()->distinct()->orderBy('dependencia_id_accesos')->pluck('dependencia_id_accesos');
        $conteos = Sistema::query()->selectRaw('estado, COUNT(*) as total')->groupBy('estado')->pluck('total', 'estado');

        return view('software.sistemas.index', compact('sistemas', 'dependencias', 'conteos', 'filtros'));
    }

    public function create(DepartamentosAccesos $departamentosAccesos)
    {

        $categorias = Categoria::where('activo', true)->with(['padre', 'campos' => fn($query) => $query->where('activo', true)])->orderBy('nombre')->get();
        $responsables = Responsable::where('activo', true)->orderBy('nombre')->get();
        $departamentos = $departamentosAccesos->listar();

        return view('software.sistemas.create', [
            'categorias' => $categorias,
            'responsables' => $responsables,
            'departamentos' => $departamentos
        ]);
    }

    public function store(sistemaRequest $request)
    {
        $data = $request->validated();

        Sistema::create($data);

        return redirect(route('software.sistemas'))->with('success', "Sistema creado correctamente");
    }


    public function edit(Sistema $sistema)
    {

        $responsables = Responsable::where('activo', true)->orderBy('nombre')->get();
        return view('software.sistemas.edit', compact('sistema', 'responsables'));
    }

    public function update(SistemaRequest $request, Sistema $sistema)
    {
        $data = $request->validated();

        $sistema->update($data);

        return redirect()
            ->route('software.sistemas')
            ->with('success', 'Sistema actualizado correctamente');
    }

    public function show(Sistema $sistema, ObtenerBitacoraSistema $obtenerBitacoraSistema): View
    {
        $historial = $obtenerBitacoraSistema->ejecutar($sistema);

        return view('software.sistemas.show', compact('sistema', 'historial'));
    }


    public function destroy(Sistema $sistema){

         $sistema->delete($sistema);

        return redirect()
            ->route('software.sistemas')
            ->with('success', 'Sistema actualizado correctamente');
    }
}
