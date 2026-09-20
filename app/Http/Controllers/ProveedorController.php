<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProveedorRequest;
use App\Models\Proveedor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProveedorController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'buscar' => ['nullable', 'string', 'max:191'],
            'estado' => ['nullable', Rule::in(['activo', 'inactivo'])],
        ]);

        $proveedores = Proveedor::query()
            ->withCount('licencias')
            ->when($filtros['buscar'] ?? null, fn ($query, $buscar) => $query->where(
                fn ($query) => $query
                    ->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('razon_social', 'like', "%{$buscar}%")
                    ->orWhere('rfc', 'like', "%{$buscar}%")
                    ->orWhere('contacto_nombre', 'like', "%{$buscar}%")
            ))
            ->when($filtros['estado'] ?? null, fn ($query, $estado) => $query->where('activo', $estado === 'activo'))
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('software.proveedores.index', compact('proveedores', 'filtros'));
    }

    public function store(StoreProveedorRequest $request): JsonResponse|RedirectResponse
    {
        $proveedor = Proveedor::create($request->validated());

        if ($request->expectsJson()) {
            return response()->json(['proveedor' => [
                'id' => $proveedor->id,
                'nombre' => $proveedor->nombre,
            ]], 201);
        }

        return back()->with('success', 'Proveedor registrado correctamente.');
    }
}
