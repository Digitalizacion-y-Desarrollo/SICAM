<?php

namespace App\Http\Controllers;

use App\Models\Bien;
use App\Models\MovimientoBien;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MovimientoController extends Controller
{
    public function index()
    {
        request()->validate(['buscar' => ['nullable', 'string', 'max:200'], 'tipo' => ['nullable', 'string', 'max:40']]);
        $movimientos = MovimientoBien::with('bien')->when(request('tipo'), fn ($q, $v) => $q->where('tipo', $v))
            ->when(request('buscar'), fn ($q, $v) => $q->whereHas('bien', fn ($q) => $q->where('folio_sicam', 'like', "%{$v}%")->orWhere('nombre', 'like', "%{$v}%")))
            ->latest('id')->paginate(20)->withQueryString();
        $tipos = MovimientoBien::select('tipo')->distinct()->orderBy('tipo')->pluck('tipo');

        return view('patrimonio.movimientos', compact('movimientos', 'tipos'));
    }

    public function create()
    {
        $bienes = Bien::orderBy('nombre')->get(['id', 'folio_sicam', 'nombre']);

        return view('patrimonio.form-movimiento', compact('bienes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(['bien_id' => ['required', 'integer', \Illuminate\Validation\Rule::exists('bienes', 'id')->whereNull('deleted_at')], 'observaciones' => ['required', 'string', 'max:5000']]);
        $movimiento = MovimientoBien::create([...$data, 'tipo' => 'NOTA', 'ip_origen' => $request->ip()]);

        return redirect()->route('patrimonio.movimientos.show', $movimiento)->with('success', 'Nota de seguimiento registrada.');
    }

    public function show(MovimientoBien $movimiento)
    {
        $movimiento->load('bien');

        return view('patrimonio.detalle-movimiento', compact('movimiento'));
    }

    public function edit(MovimientoBien $movimiento)
    {
        return view('patrimonio.form-movimiento', compact('movimiento'));
    }

    public function update(Request $request, MovimientoBien $movimiento)
    {
        $movimiento->update($request->validate(['observaciones' => ['required', 'string', 'max:5000']]));

        return redirect()->route('patrimonio.movimientos.show', $movimiento)->with('success', 'Observaciones del movimiento actualizadas.');
    }

    public function destroy(MovimientoBien $movimiento)
    {
        if ($movimiento->tipo !== 'NOTA') throw ValidationException::withMessages(['movimiento' => 'Los movimientos automáticos forman parte del historial del bien. Solo se pueden eliminar notas manuales.']);
        $movimiento->delete();

        return redirect()->route('patrimonio.movimientos.index')->with('success', 'Nota eliminada. Se conservó la auditoría.');
    }
}
