<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAsignacionRequest;
use App\Models\Asignacion;
use App\Models\Bien;
use App\Models\Responsable;
use App\Services\AsignarBien;
use App\Services\DepartamentosAccesos;
use Illuminate\Validation\ValidationException;

class AsignacionController extends Controller
{
    public function __construct(private readonly DepartamentosAccesos $departamentosAccesos) {}

    public function index()
    {
        request()->validate(['buscar' => ['nullable', 'string', 'max:200'], 'estado' => ['nullable', 'in:vigente,cerrada']]);
        $asignaciones = Asignacion::with(['bien', 'responsable'])
            ->when(request('buscar'), fn ($q, $buscar) => $q->whereHas('bien', fn ($q) => $q->where('nombre', 'like', "%{$buscar}%")->orWhere('folio_sicam', 'like', "%{$buscar}%")))
            ->when(request('estado') === 'vigente', fn ($q) => $q->whereNull('fecha_fin'))
            ->when(request('estado') === 'cerrada', fn ($q) => $q->whereNotNull('fecha_fin'))->latest('id')->paginate(15)->withQueryString();

        return view('patrimonio.asignaciones', compact('asignaciones'));
    }

    public function create()
    {
        return $this->form();
    }

    public function edit(Asignacion $asignacion)
    {
        if ($asignacion->fecha_fin) {
            throw ValidationException::withMessages(['asignacion' => 'La asignación está cerrada; registra un nuevo resguardo.']);
        }

        return $this->form($asignacion);
    }

    private function form(?Asignacion $asignacion = null)
    {
        $bienes = Bien::orderBy('nombre')->get(['id', 'nombre', 'folio_sicam']);
        $responsables = Responsable::where('activo', true)->orderBy('nombre')->get();
        $departamentos = $this->departamentosAccesos->listar();

        return view('patrimonio.form-asignacion', compact('asignacion', 'bienes', 'responsables', 'departamentos'));
    }

    public function show(Asignacion $asignacion)
    {
        $asignacion->load(['bien', 'responsable']);

        return view('patrimonio.detalle-asignacion', compact('asignacion'));
    }

    public function store(StoreAsignacionRequest $request, AsignarBien $service)
    {
        $bien = Bien::whereKey($request->integer('bien_id'))->lockForUpdate()->firstOrFail();
        if ($bien->asignaciones()->whereNull('fecha_fin')->exists()) {
            throw ValidationException::withMessages(['bien_id' => 'El bien ya tiene un resguardo vigente. Usa la opción Reasignar.']);
        }
        $asignacion = $service->asignar($bien, $request->validated());

        return redirect()->route('patrimonio.asignaciones.show', $asignacion)->with('success', 'Asignación registrada.');
    }

    public function update(StoreAsignacionRequest $request, Asignacion $asignacion, AsignarBien $service)
    {
        $bien = Bien::whereKey($asignacion->bien_id)->lockForUpdate()->firstOrFail();
        $asignacion->refresh();
        if ($asignacion->fecha_fin || $request->integer('bien_id') !== $bien->id) {
            throw ValidationException::withMessages(['asignacion' => 'No se puede modificar el bien de un resguardo ni reasignar uno cerrado.']);
        }
        $nueva = $service->asignar($bien, $request->validated());

        return redirect()->route('patrimonio.asignaciones.show', $nueva)->with('success', 'Bien reasignado. Se conservó el resguardo anterior.');
    }

    public function destroy(Asignacion $asignacion, AsignarBien $service)
    {
        $bien = Bien::whereKey($asignacion->bien_id)->lockForUpdate()->firstOrFail();
        $asignacion->refresh();
        if ($asignacion->fecha_fin) {
            throw ValidationException::withMessages(['asignacion' => 'La asignación ya está cerrada.']);
        }
        $service->asignar($bien, ['responsabilidad' => 'ninguna', 'dependencia_id_accesos' => $bien->dependencia_id_accesos, 'area_id_accesos' => $bien->area_id_accesos]);

        return redirect()->route('patrimonio.asignaciones.index')->with('success', 'Asignación finalizada. El bien quedó disponible.');
    }
}
