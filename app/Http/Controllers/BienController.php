<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBienRequest;
use App\Http\Requests\UpdateBienRequest;
use App\Models\Bien;
use App\Models\Categoria;
use App\Models\Responsable;
use App\Services\ActualizarBien;
use App\Services\DepartamentosAccesos;
use App\Services\QrBien;
use App\Services\RegistrarBien;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BienController extends Controller
{
    public function index(): View
    {
        $bienes = Bien::with(['categoria', 'asignacionActual.responsable'])
            ->when(request('buscar'), fn ($query, $buscar) => $query->where(function ($query) use ($buscar) {
                $query->where('nombre', 'like', "%{$buscar}%")->orWhere('folio_sicam', 'like', "%{$buscar}%")
                    ->orWhere('numero_patrimonial', 'like', "%{$buscar}%")->orWhere('numero_serie', 'like', "%{$buscar}%");
            }))
            ->when(request('categoria_id'), fn ($query, $categoriaId) => $query->where('categoria_id', $categoriaId))
            ->when(request('estado'), fn ($query, $estado) => $query->where('estado', $estado))
            ->when(request('dependencia'), fn ($query, $dependencia) => $query->where('dependencia_id_accesos', $dependencia))
            ->when(request('area'), fn ($query, $area) => $query->where('area_id_accesos', $area))
            ->when(request('responsable_id'), fn ($query, $responsableId) => $query->whereHas(
                'asignacionActual',
                fn ($asignacion) => $asignacion->where('responsable_id', $responsableId)
            ))
            ->latest('id')->paginate(15)->withQueryString();

        $categorias = Categoria::where('activo', true)->with('padre')->orderBy('nombre')->get();
        $dependencias = Bien::query()->whereNotNull('dependencia_id_accesos')->where('dependencia_id_accesos', '!=', '')
            ->distinct()->orderBy('dependencia_id_accesos')->pluck('dependencia_id_accesos');
        $areas = Bien::query()->whereNotNull('area_id_accesos')->where('area_id_accesos', '!=', '')
            ->when(request('dependencia'), fn ($query, $dependencia) => $query->where('dependencia_id_accesos', $dependencia))
            ->distinct()->orderBy('area_id_accesos')->pluck('area_id_accesos');
        $responsables = Responsable::where('activo', true)->orderBy('nombre')->get();

        return view('patrimonio.bienes', compact('bienes', 'categorias', 'dependencias', 'areas', 'responsables'));
    }

    public function create(DepartamentosAccesos $departamentosAccesos): View
    {
        $categorias = Categoria::where('activo', true)->with(['padre', 'campos' => fn ($query) => $query->where('activo', true)])->orderBy('nombre')->get();
        $responsables = Responsable::where('activo', true)->orderBy('nombre')->get();
        $departamentos = $departamentosAccesos->listar();

        return view('patrimonio.nuevo-bien', compact('categorias', 'responsables', 'departamentos'));
    }

    public function store(StoreBienRequest $request, RegistrarBien $registrar): RedirectResponse
    {
        $bien = $registrar->registrar($request->validated(), $request->file('fotografia'), $request->ip());

        return redirect()->route('patrimonio.bienes')->with('success', "Bien registrado correctamente: {$bien->folio_sicam} · {$bien->numero_patrimonial}.");
    }

    public function show(Bien $bien): View
    {
        $bien->load(['categoria', 'valores.campo', 'asignacionActual.responsable']);
        $movimientos = $bien->movimientos()->latest('id')->paginate(10);

        return view('patrimonio.detalle-bien', compact('bien', 'movimientos'));
    }

    public function edit(Bien $bien, DepartamentosAccesos $departamentosAccesos): View
    {
        $bien->load(['valores', 'asignacionActual']);
        $categorias = Categoria::where('activo', true)->with(['padre', 'campos' => fn ($query) => $query->where('activo', true)])->orderBy('nombre')->get();
        $responsables = Responsable::where('activo', true)->orderBy('nombre')->get();
        $departamentos = $departamentosAccesos->listar();
        $defaults = [...$bien->getAttributes(), 'campos' => $bien->valores->pluck('valor', 'campo_categoria_id')->all(),
            'responsabilidad' => strtolower($bien->asignacionActual?->tipo_responsabilidad ?? 'ninguna'),
            'responsable_id' => $bien->asignacionActual?->responsable_id];

        return view('patrimonio.nuevo-bien', compact('bien', 'categorias', 'responsables', 'defaults', 'departamentos'));
    }

    public function update(UpdateBienRequest $request, Bien $bien, ActualizarBien $actualizar): RedirectResponse
    {
        $actualizar->actualizar($bien, $request->validated(), $request->file('fotografia'));

        return redirect()->route('patrimonio.bienes.show', $bien)->with('success', 'Bien actualizado correctamente.');
    }

    public function destroy(Bien $bien, ActualizarBien $actualizar): RedirectResponse
    {
        $actualizar->eliminar($bien);

        return redirect()->route('patrimonio.bienes')->with('success', 'Bien eliminado. Se conservó el historial de auditoría.');
    }

    public function qr(Bien $bien, QrBien $qr)
    {
        $formato = request()->validate(['formato' => ['nullable', Rule::in(['svg', 'png'])]])['formato'] ?? 'svg';

        return response($qr->imagen($bien, $formato))->header('Content-Type', $formato === 'png' ? 'image/png' : 'image/svg+xml')
            ->header('Content-Disposition', 'attachment; filename="'.$bien->folio_sicam.'.'.$formato.'"');
    }
}
