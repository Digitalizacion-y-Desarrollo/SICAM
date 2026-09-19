<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use App\Models\Bien;
use App\Models\Categoria;
use App\Models\MovimientoBien;
use App\Models\Responsable;

class ResumenPatrimonioController extends Controller
{
    public function __invoke()
    {
        $metricas = [
            'Bienes registrados' => Bien::count(), 'Disponibles' => Bien::where('estado', 'DISPONIBLE')->count(),
            'Asignados' => Bien::where('estado', 'ASIGNADO')->count(), 'En resguardo' => Bien::where('estado', 'EN_RESGUARDO')->count(),
            'Responsables activos' => Responsable::where('activo', true)->count(),
            'Asignaciones vigentes' => Asignacion::whereNull('fecha_fin')->count(),
        ];
        $categorias = Categoria::withCount('bienes')->orderBy('nombre')->get();
        $movimientos = MovimientoBien::with('bien')->latest('id')->limit(8)->get();

        return view('patrimonio.resumen', compact('metricas', 'categorias', 'movimientos'));
    }
}
