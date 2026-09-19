<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCampoCategoriaRequest;
use App\Http\Requests\StoreCategoriaRequest;
use App\Models\CampoCategoria;
use App\Models\Categoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoriaController extends Controller
{
    public function index(): View
    {
        $categorias = Categoria::withCount('bienes')->with('hijas')->whereNull('categoria_padre_id')->orderBy('nombre')->get();
        $seleccionada = Categoria::with(['padre','campos'])->withCount('bienes')->find(request('categoria')) ?? Categoria::with(['padre','campos'])->withCount('bienes')->orderBy('nombre')->first();
        return view('patrimonio.categorias', compact('categorias', 'seleccionada'));
    }

    public function store(StoreCategoriaRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['nombre']).'-'.Str::lower(Str::random(5));
        $categoria = Categoria::create($data);
        return redirect()->route('patrimonio.categorias', ['categoria' => $categoria])->with('success', 'Categoría creada correctamente.');
    }

    public function storeCampo(StoreCampoCategoriaRequest $request, Categoria $categoria): RedirectResponse
    {
        $data = $request->validated();
        $data['clave'] = Str::limit(Str::slug($data['nombre'], '_'), 65, '').'_'.Str::lower(Str::random(8));
        $data['requerido'] = $request->boolean('requerido');
        $data['orden'] = $categoria->campos()->max('orden') + 1;
        $categoria->campos()->create($data);
        return back()->with('success', 'Campo dinámico agregado.');
    }

    public function destroyCampo(CampoCategoria $campo): RedirectResponse
    {
        $campo->update(['activo' => false, 'requerido' => false]);
        return back()->with('success', 'Campo dinámico eliminado.');
    }

    public function edit(Categoria $categoria): View
    {
        $categorias = Categoria::where('id', '!=', $categoria->id)->orderBy('nombre')->get();

        return view('patrimonio.editar-categoria', compact('categoria', 'categorias'));
    }

    public function update(StoreCategoriaRequest $request, Categoria $categoria): RedirectResponse
    {
        $data = $request->validated();
        $parentId = $data['categoria_padre_id'] ?? null;
        $visited = [$categoria->id];
        while ($parentId) {
            if (in_array((int) $parentId, $visited, true)) throw \Illuminate\Validation\ValidationException::withMessages(['categoria_padre_id' => 'La categoría no puede depender de sí misma ni de sus descendientes.']);
            $visited[] = (int) $parentId;
            $parentId = Categoria::findOrFail($parentId)->categoria_padre_id;
        }
        $categoria->update($data);

        return redirect()->route('patrimonio.categorias', ['categoria' => $categoria])->with('success', 'Categoría actualizada.');
    }

    public function destroy(Categoria $categoria): RedirectResponse
    {
        if ($categoria->bienes()->exists() || $categoria->hijas()->exists()) throw \Illuminate\Validation\ValidationException::withMessages(['categoria' => 'No se puede eliminar una categoría con bienes o subcategorías.']);
        $categoria->delete();

        return redirect()->route('patrimonio.categorias')->with('success', 'Categoría eliminada.');
    }

    public function editCampo(CampoCategoria $campo): View
    {
        return view('patrimonio.editar-campo', compact('campo'));
    }

    public function updateCampo(StoreCampoCategoriaRequest $request, CampoCategoria $campo): RedirectResponse
    {
        if ($campo->tipo !== $request->input('tipo') && \App\Models\ValorBien::where('campo_categoria_id', $campo->id)->exists()) throw \Illuminate\Validation\ValidationException::withMessages(['tipo' => 'Este campo ya tiene valores registrados; conserva su tipo o crea un campo nuevo.']);
        $campo->update([...$request->validated(), 'requerido' => $request->boolean('requerido')]);

        return redirect()->route('patrimonio.categorias', ['categoria' => $campo->categoria_id])->with('success', 'Campo actualizado.');
    }
}
