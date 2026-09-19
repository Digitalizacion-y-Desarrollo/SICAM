<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;

class AuditoriaController extends Controller
{
    public function index()
    {
        request()->validate(['accion' => ['nullable', 'in:CREAR,ACTUALIZAR,ELIMINAR'], 'entidad' => ['nullable', 'string', 'max:100']]);
        $auditorias = Auditoria::when(request('accion'), fn ($q, $v) => $q->where('accion', $v))
            ->when(request('entidad'), fn ($q, $v) => $q->where('entidad', $v))->latest('id')->paginate(30)->withQueryString();

        return view('patrimonio.auditoria', compact('auditorias'));
    }
}
