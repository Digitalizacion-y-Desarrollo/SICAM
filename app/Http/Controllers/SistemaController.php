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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SistemaController extends Controller
{
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

        return redirect(route('software.sistemas'))->with('message', "Sistema creado correctamente");
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
            ->with('message', 'Sistema actualizado correctamente');
    }

    public function show (Sistema $sistema){

        return view('software.sistemas.show', compact('sistema'));
    }


    public function destroy(Sistema $sistema){

         $sistema->delete($sistema);

        return redirect()
            ->route('software.sistemas')
            ->with('message', 'Sistema actualizado correctamente');
    }
}
