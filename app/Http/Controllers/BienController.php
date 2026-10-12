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
use App\Support\AlcanceAreaUsuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BienController extends Controller
{
    public function index(): View
    {
        $departamento = session('accesos.departamento');
        $roles = session('accesos.roles', []);
        $esSuperusuario = is_array($roles) && in_array('superuser', $roles, true);
        $dependenciaDelUsuario = AlcanceAreaUsuario::ubicacion()['dependencia'];
        $dependenciaFiltro = $esSuperusuario
            ? request('dependencia')
            : $dependenciaDelUsuario;

        $bienes = Bien::with(['categoria', 'dependencia', 'area', 'asignacionActual.responsable'])
            ->when(request('buscar'), fn ($query, $buscar) => $query->where(function ($query) use ($buscar) {
                $query->where('nombre', 'like', "%{$buscar}%")->orWhere('folio_sicam', 'like', "%{$buscar}%")
                    ->orWhere('numero_patrimonial', 'like', "%{$buscar}%")->orWhere('numero_serie', 'like', "%{$buscar}%");
            }))
            ->when(request('categoria_id'), fn ($query, $categoriaId) => $query->where('categoria_id', $categoriaId))
            ->when(request('estado'), fn ($query, $estado) => $query->where('estado', $estado))
            ->when($dependenciaFiltro, fn ($query, $dependencia) => $query->where('dependencia_id_accesos', $dependencia))
            ->when(request('area'), fn ($query, $area) => $query->where('area_id_accesos', $area))
            ->when(request('responsable_id'), fn ($query, $responsableId) => $query->whereHas(
                'asignacionActual',
                fn ($asignacion) => $asignacion->where('responsable_id', $responsableId)
            ))
            ->latest('id')->paginate(15)->withQueryString();

        $categorias = Categoria::where('activo', true)->with('padre')->orderBy('nombre')->get();
        $dependencias = Bien::query()->whereNotNull('dependencia_id_accesos')->where('dependencia_id_accesos', '!=', '')
            ->when(! $esSuperusuario && $dependenciaFiltro, fn ($query) => $query->where('dependencia_id_accesos', $dependenciaFiltro))
            ->distinct()->orderBy('dependencia_id_accesos')->pluck('dependencia_id_accesos');
        $areas = Bien::query()->whereNotNull('area_id_accesos')->where('area_id_accesos', '!=', '')
            ->when($dependenciaFiltro, fn ($query, $dependencia) => $query->where('dependencia_id_accesos', $dependencia))
            ->distinct()->orderBy('area_id_accesos')->pluck('area_id_accesos');
        $responsables = Responsable::where('activo', true)->orderBy('nombre')->get();

        return view('patrimonio.bienes', compact(
            'bienes', 'categorias', 'dependencias', 'areas', 'responsables', 'departamento',
            'dependenciaFiltro', 'esSuperusuario'
        ));
    }

    public function create(DepartamentosAccesos $departamentosAccesos): View
    {
        $categorias = Categoria::where('activo', true)->with(['padre', 'campos' => fn ($query) => $query->where('activo', true)])->orderBy('nombre')->get();
        $responsables = Responsable::where('activo', true)->orderBy('nombre')->get();
        $departamentos = $departamentosAccesos->listar();
        
        return view('patrimonio.nuevo-bien', compact('categorias', 'responsables', 'departamentos') + $this->datosUbicacionFormulario());
    }

    public function store(StoreBienRequest $request, RegistrarBien $registrar): RedirectResponse
    {
        $bien = $registrar->registrar($this->forzarUbicacionUsuario($request->validated()), $request->file('fotografia'), $request->ip());

        return redirect()->route('patrimonio.bienes')->with('success', "Bien registrado correctamente: {$bien->folio_sicam} · {$bien->numero_patrimonial}.");
    }

    public function show(Bien $bien): View
    {
        $bien->load(['categoria', 'dependencia', 'area', 'valores.campo', 'asignacionActual.responsable']);
        $movimientos = $bien->movimientos()->latest('id')->paginate(10);

        return view('patrimonio.detalle-bien', compact('bien', 'movimientos'));
    }

    public function edit(Bien $bien, DepartamentosAccesos $departamentosAccesos): View
    {
        $bien->load(['valores', 'asignacionActual']);
        $categorias = Categoria::where('activo', true)->with(['padre', 'campos' => fn ($query) => $query->where('activo', true)])->orderBy('nombre')->get();
        $responsables = Responsable::where('activo', true)->orderBy('nombre')->get();
        $departamentos = $departamentosAccesos->listar();
        $defaults = [
            ...$bien->getAttributes(),
            'campos' => $bien->valores->pluck('valor', 'campo_categoria_id')->all(),
            'responsabilidad' => strtolower($bien->asignacionActual?->tipo_responsabilidad ?? 'ninguna'),
            'responsable_id' => $bien->asignacionActual?->responsable_id,
        ];

        return view('patrimonio.nuevo-bien', compact('bien', 'categorias', 'responsables', 'defaults', 'departamentos') + $this->datosUbicacionFormulario());
    }

    public function update(UpdateBienRequest $request, Bien $bien, ActualizarBien $actualizar): RedirectResponse
    {
        $actualizar->actualizar($bien, $this->forzarUbicacionUsuario($request->validated()), $request->file('fotografia'));

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

        return response($qr->etiqueta($bien, $formato))->header('Content-Type', $formato === 'png' ? 'image/png' : 'image/svg+xml')
            ->header('Content-Disposition', 'attachment; filename="'.$bien->folio_sicam.'.'.$formato.'"');
    }

    private function datosUbicacionFormulario(): array
    {
        return ['ubicacionUsuario' => AlcanceAreaUsuario::ubicacion(), 'esSuperusuario' => AlcanceAreaUsuario::esSuperusuario()];
    }

    private function forzarUbicacionUsuario(array $datos): array
    {
        // Un usuario con permiso para registrar, pero sin una ubicación enviada por
        // Accesos, no tiene un alcance que aplicar. En ese caso se debe conservar
        // la ubicación válida elegida en el formulario; eliminarla provoca que el
        // INSERT falle porque dependencia_id_accesos es obligatoria.
        if (AlcanceAreaUsuario::esSuperusuario() || ! session()->has('accesos.departamento')) {
            return $datos;
        }

        $ubicacion = AlcanceAreaUsuario::ubicacion();

        return array_filter([
            ...$datos,
            'dependencia_id_accesos' => $ubicacion['dependencia'],
            'area_id_accesos' => $ubicacion['area'],
        ], fn ($valor, $campo) => ! in_array($campo, ['dependencia_id_accesos', 'area_id_accesos'], true) || filled($valor), ARRAY_FILTER_USE_BOTH);
    }
}
