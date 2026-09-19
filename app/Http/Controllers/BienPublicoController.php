<?php

namespace App\Http\Controllers;

use App\Models\Bien;
use Illuminate\Support\Facades\Storage;

class BienPublicoController extends Controller
{
    public function show(string $publicId)
    {
        $bien = Bien::with('categoria')->where('public_id', $publicId)->firstOrFail();
        $ficha = $bien->only(['public_id', 'folio_sicam', 'numero_patrimonial', 'nombre', 'marca', 'modelo', 'estado', 'dependencia_id_accesos', 'area_id_accesos']);
        $ficha['categoria'] = $bien->categoria?->nombre;
        $ficha['fotografia'] = (bool) $bien->fotografia_path;

        return response()->view('patrimonio.bien-publico', compact('ficha'))->header('X-Robots-Tag', 'noindex, nofollow');
    }

    public function fotografia(string $publicId)
    {
        $bien = Bien::where('public_id', $publicId)->firstOrFail();
        abort_unless($bien->fotografia_path && Storage::disk('local')->exists($bien->fotografia_path), 404);

        return Storage::disk('local')->response($bien->fotografia_path, null, ['X-Content-Type-Options' => 'nosniff']);
    }
}
