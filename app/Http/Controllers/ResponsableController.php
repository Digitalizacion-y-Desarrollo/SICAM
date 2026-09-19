<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreResponsableRequest;
use App\Http\Requests\UpdateResponsableRequest;
use App\Models\Responsable;
use App\Services\DepartamentosAccesos;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ResponsableController extends Controller
{
    public function index(DepartamentosAccesos $departamentosAccesos): View
    {
        $responsables = Responsable::query()->withCount(['asignaciones as bienes_asignados' => fn ($q) => $q->whereNull('fecha_fin')])
            ->when(request('buscar'), fn ($q, $v) => $q->where(fn ($q) => $q->where('numero_empleado', 'like', "%{$v}%")->orWhere('nombre', 'like', "%{$v}%")->orWhere('apellido_paterno', 'like', "%{$v}%")->orWhere('cargo', 'like', "%{$v}%")))
            ->when(request()->filled('estado'), fn ($q) => $q->where('activo', request('estado') === 'activo'))
            ->latest()->paginate(10)->withQueryString();
        $departamentos = $departamentosAccesos->listar();

        return view('patrimonio.responsables', compact('responsables', 'departamentos'));
    }

    public function store(StoreResponsableRequest $request): RedirectResponse|JsonResponse
    {
        $responsable = Responsable::create($request->validated());

        if ($request->expectsJson()) {
            return response()->json(['responsable' => [
                'id' => $responsable->id,
                'nombre_completo' => $responsable->nombre_completo,
                'cargo' => $responsable->cargo,
            ]], 201);
        }

        return back()->with('success', 'Responsable registrado correctamente.');
    }

    public function update(UpdateResponsableRequest $request, Responsable $responsable): RedirectResponse
    {
        $responsable = Responsable::whereKey($responsable->id)->lockForUpdate()->firstOrFail();
        $data = $request->validated();
        $data['area_id_accesos'] = $data['area_id_accesos'] ?? null;
        if ($responsable->asignaciones()->whereNull('fecha_fin')->exists() &&
            ($data['dependencia_id_accesos'] !== $responsable->dependencia_id_accesos || $data['area_id_accesos'] !== $responsable->area_id_accesos || (isset($data['activo']) && ! $data['activo']))) {
            throw ValidationException::withMessages(['responsable' => 'Reasigna o cierra los resguardos vigentes antes de cambiar la ubicación o desactivar al responsable.']);
        }
        $responsable->update($data);

        return redirect()->route('patrimonio.responsables.show', $responsable)->with('success', 'Responsable actualizado.');
    }

    public function destroy(Responsable $responsable): RedirectResponse
    {
        $responsable = Responsable::whereKey($responsable->id)->lockForUpdate()->firstOrFail();
        if ($responsable->asignaciones()->whereNull('fecha_fin')->exists()) {
            throw ValidationException::withMessages(['responsable' => 'El responsable tiene resguardos vigentes. Reasígnalos antes de eliminarlo.']);
        }
        $responsable->delete();

        return redirect()->route('patrimonio.responsables')->with('success', 'Responsable eliminado.');
    }

    public function show(Responsable $responsable): View
    {
        $asignaciones = $responsable->asignaciones()->with('bien')->latest('id')->paginate(15);

        return view('patrimonio.detalle-responsable', compact('responsable', 'asignaciones'));
    }

    public function edit(Responsable $responsable, DepartamentosAccesos $departamentosAccesos): View
    {
        $departamentos = $departamentosAccesos->listar();

        return view('patrimonio.editar-responsable', compact('responsable', 'departamentos'));
    }
}
