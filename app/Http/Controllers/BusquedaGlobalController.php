<?php

namespace App\Http\Controllers;

use App\Services\BuscarEnSicam;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BusquedaGlobalController extends Controller
{
    public function __invoke(Request $request, BuscarEnSicam $busqueda): JsonResponse
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
        ]);

        return response()->json([
            'resultados' => $busqueda->ejecutar((string) ($data['q'] ?? '')),
        ]);
    }
}
